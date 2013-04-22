<?php
/**
 * Webwork
 * Copyright (C) 2011 IceFlame.net
 *
 * Permission to use, copy, modify, and/or distribute this software for
 * any purpose with or without fee is hereby granted, provided that the
 * above copyright notice and this permission notice appear in all copies.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE
 * FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY
 * DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER
 * IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING
 * OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 *
 * @package     Webwork
 * @version     0.1-dev
 * @link        http://www.iceflame.net
 * @license     ISC License (http://www.opensource.org/licenses/ISC)
 */

/**
 * Webwork Core
 *
 * @author  Christian Neff <christian.neff@gmail.com>
 */

define('WW_ENGINE_PATH', __DIR__);

include_once WW_ENGINE_PATH.'/includes/config.php';

if ($config['enable_multisite'] && isset($config['sites'])) {
    // This is a multi-site installation, so we need to know the current domain name
    $domain = $_SERVER['SERVER_NAME'];

    // Check if there is a site for the current domain, fall back to default site otherwise
    if (isset($config['sites'][$domain])) {
        $activeSite = $config['sites'][$domain];
    } else {
        $activeSite = isset($config['default_site']) ? $config['default_site'] : 'default';
    }
} else {
    // This is a single-site installation, hence we use the default site
    $activeSite = 'default';
}

define('WW_SITE_NAME', $activeSite);
define('WW_SITE_PATH', WW_ENGINE_PATH.'/websites/'.WW_SITE_NAME);

try {
    require_once WW_ENGINE_PATH.'/includes/autoloader.php';
    require_once WW_ENGINE_PATH.'/libraries/functions.php';

    System::startup();

    define('WW_ROOT_URL', System::$settings['core']['url']);

    Session::init();

    // Fetch list of language packs
    $languages = Cache::read('languages');
    if (!isset($languages)) {
        $result = System::$db->select('@PREFIX@languages');

        while ($data = $result->fetchAssoc()) {
            $languages[$data['id']] = array(
                'name'      => $data['name'],
                'direction' => $data['direction'],
                'locales'   => explode(',', $data['locales'])
            );
        }

        Cache::store('languages', $languages);
    }

    // Detect the user's preferred language
    if (isset(Session::$data['language'])) {
        // There was found a language setting in the user's session
        $language = Session::$data['language'];
    } elseif ($browserLangs = Http::getAcceptLanguage()) {
        // We can use the browser language: Try to find the best match
        foreach (array_keys($browserLangs) as $browserLang) {
            if (isset($languages[$browserLang])) {
                $language = $browserLang;
                break;
            }
        }
    }

    // If no preferred language was detected, fall back to the default language
    if (!isset($language))
        $language = System::$settings['core']['lang'];

    setlocale(LC_ALL, $languages[$language]['locales']);

    $t = new Translations($language);

    Template::setTitle(System::$settings['core']['site_name']);

    @include WW_SITE_PATH.'/includes/global.php';
    
    // Split the path into its parts. Use frontpage path if no path is specified.
    $path = isset($_GET['p']) && $_GET['p'] != '' ? $_GET['p'] : System::$settings['core']['frontpage'];
    System::loadModuleFromPath($path);
} catch (Exception $error) {
    $tpl = new Template('error');
    if ($config['enable_debugmode'])
        $tpl->set('debug', $error->getMessage());
    $tpl->render();
}
