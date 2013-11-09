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
 * @author   Christian Neff <christian.neff@gmail.com>
 */

define('WW_ENGINE_PATH', dirname($_SERVER['SCRIPT_FILENAME']));

include_once WW_ENGINE_PATH.'/includes/config.php';
require_once WW_ENGINE_PATH.'/includes/autoloader.php';
require_once WW_ENGINE_PATH.'/includes/exception.php';
require_once WW_ENGINE_PATH.'/includes/functions.php';

if ($CONFIG['enable_multisite'] && isset($CONFIG['sites'])) {
    // This is a multi-site installation, so we need to know the current domain name
    $domain = $_SERVER['SERVER_NAME'];

    // Check if there is a site for the current domain, fall back to default site otherwise
    if (isset($CONFIG['sites'][$domain])) {
        $activeSite = $CONFIG['sites'][$domain];
    } else {
        $activeSite = isset($CONFIG['default_site']) ? $CONFIG['default_site'] : 'default';
    }
} else {
    // This is a single-site installation, hence we use the default site
    $activeSite = 'default';
}

define('WW_SITE_NAME', $activeSite);
define('WW_SITE_PATH', WW_ENGINE_PATH.'/websites/'.WW_SITE_NAME);
define('WW_SHARED_PATH', WW_ENGINE_PATH.'/websites/shared');
