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

define('WW_ENGINE_PATH', dirname($_SERVER['SCRIPT_FILENAME']));

try {
    @include_once WW_ENGINE_PATH.'/includes/config.php';

    if (defined('WW_ENABLE_MULTISITE') && WW_ENABLE_MULTISITE) {
        // This is a multi-site installation, so we need to know the current domain name
        $domain = $_SERVER['SERVER_NAME'];
        
        // Check if there is a site for the current domain, fall back to default site otherwise
        if (is_dir(WW_ENGINE_PATH.'/sites/'.$domain)) {
            $activeSite = $domain;
        } else {
            $activeSite = defined('WW_DEFAULT_SITE') ? WW_DEFAULT_SITE : 'default';
        }
    } else {
        // This is a single-site installation, hence we use the default site
        $activeSite = 'default';
    }
    
    define('WW_SITE_PATH', WW_ENGINE_PATH.'/sites/'.$activeSite);

    require_once WW_ENGINE_PATH.'/includes/autoloader.php';
    require_once WW_ENGINE_PATH.'/includes/functions.php';
    
    Settings::init();

    $db = Database::createDriver();
    
    $session = new Session();
    $user = new User($session->assignedUser);

    // Fetch list of language packs
    $languages = Cache::read('languages');
    if (!isset($languages)) {
        $sql = 'SELECT * FROM @PREFIX@languages';
        $result = $db->query($sql);

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
    if (isset($session->data['language'])) {
        // There was found a language setting in the user's session
        $language = $session->data['language'];
    } elseif ($browserLangs = Http::getAcceptLanguage()) {
        // We can use the browser language: Try to find the best match
        foreach (array_keys($browserLangs) as $lang) {
            if (isset($languages[$lang])) {
                $language = $lang;
                break;
            }
        }
    }

    // If no preferred language was detected, fall back to the default language
    if (!isset($language))
        $language = Settings::get('core', 'lang');

    setlocale(LC_ALL, $languages[$language]['locales']);
    
    $t = new Translations($language);

    Template::setTitle(Settings::get('core', 'site_name'));

    $path = new Path($_GET['p'], Settings::get('core', 'frontpage'));

    $module = $path->controller;
    
    @include WW_SITE_PATH.'/includes/global.php';
    
    $moduleFile = WW_SITE_PATH.'/modules/'.$module.'/controller.php';
    if (file_exists($moduleFile)) {
        include $moduleFile;
    } else {
        showNotFoundError();
    }
} catch (Exception $error) {
    die('<strong>Error:</strong> '.$error->getMessage());
}
