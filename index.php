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

    require_once WW_ENGINE_PATH.'/includes/autoloader.php';
    require_once WW_ENGINE_PATH.'/includes/functions/core.php';
    
    Settings::init();

    $db = Database::createDriver();
    
    $session = new Session();
    $user = new User($session->assignedUser);

    Lang::init(Settings::get('core', 'lang'));

    Template::setTitle(Settings::get('core', 'site_name'));

    $path = new Path($_GET['p'], Settings::get('core', 'frontpage'));

    $module = $path->controller;
    
    @include WW_ENGINE_PATH.'/includes/global.php';
    
    $moduleFile = WW_ENGINE_PATH.'/modules/'.$module.'/controller.php';
    if (file_exists($moduleFile)) {
        include $moduleFile;
    } else {
        showNotFoundError();
    }
} catch (Exception $error) {
    die('<strong>Error:</strong> '.$error->getMessage());
}
