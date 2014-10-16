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
 * @package  FlameCore\Webwork
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

namespace FlameCore\Webwork;

use Symfony\Component\HttpFoundation\Request;

/**
 * Webwork Core
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */

if (!is_readable(WW_ENGINE_PATH.'/config.php'))
    exit('No configuration file found. Please copy the file "config.php.dist" to "config.php".');

if (!is_readable(WW_ENGINE_PATH.'/vendor/autoload.php'))
    exit('No vendor autoloader found. Please make sure that you have installed the required libraries using Composer.');

$CONFIG = array();

require_once WW_ENGINE_PATH.'/config.php';

require_once WW_ENGINE_PATH.'/includes/errorhandler.php';
require_once WW_ENGINE_PATH.'/includes/autoloader.php';

require_once WW_ENGINE_PATH.'/vendor/autoload.php';

require_once WW_ENGINE_PATH.'/includes/functions.php';

$request = Request::createFromGlobals();

if (config('enable_multisite') && $sites = config('sites')) {
    // This is a multi-site installation, so we need to know the current domain name
    $domain = $request->server->get('SERVER_NAME');

    // Check if there is a site for the current domain, fall back to default site otherwise
    if (isset($sites[$domain])) {
        $active_site = $sites[$domain];
    } else {
        $active_site = config('default_site', 'default');
    }
} else {
    // This is a single-site installation, hence we use the default site
    $active_site = 'default';
}

define('WW_SITE_NAME', $active_site);
define('WW_SITE_PATH', WW_ENGINE_PATH.'/websites/'.WW_SITE_NAME);
define('WW_SHARED_PATH', WW_ENGINE_PATH.'/websites/shared');

define('WW_CACHE_PATH', WW_ENGINE_PATH.'/cache/'.WW_SITE_NAME);

System::startup();

International::init($request);
