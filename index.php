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

try {
    require_once 'includes/constants.php';
    require_once 'includes/autoloader.php';
    require_once 'includes/functions.php';

    Settings::init();

    $db = Database::createDriver();

    Lang::init(Settings::get('core', 'lang'));

    Template::setTitle(Settings::get('core', 'site_name'));
    
    $session = new Session();
    $user = new User($session->assignedUser);

    $path = new Path($_GET['p'], Settings::get('core', 'frontpage'));
    
    if (file_exists(WW_DIR_INCLUDES.'/global.php'))
        include WW_DIR_INCLUDES.'/global.php';

    $module = $path->controller;
    $moduleFile = WW_DIR_MODULES.'/'.$module.'.php';
    
    if (!file_exists($moduleFile))
        showNotFoundError();
    
    include $moduleFile;
} catch (Exception $error) {
    die('<strong>Error:</strong> '.$error->getMessage());
}
