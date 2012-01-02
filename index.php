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

define('WW_DIR_ROOT', __DIR__);
define('WW_DIR_INCLUDES', WW_DIR_ROOT.'/includes');
define('WW_DIR_MODULES', WW_DIR_ROOT.'/modules');
define('WW_DIR_SETTINGS', WW_DIR_ROOT.'/settings');
define('WW_DIR_TEMP', WW_DIR_ROOT.'/temp');
define('WW_DIR_CACHE', WW_DIR_TEMP.'/cache');
define('WW_DIR_ASSETS', WW_DIR_ROOT.'/assets');
define('WW_DIR_THEMES', WW_DIR_ROOT.'/themes');

// load bootstrap
require_once WW_DIR_INCLUDES.'/bootstrap.php';

// set page title
Template::setTitle(Settings::get('core', 'site_name'));

// get module from params
$path = new Path($_GET['p']);

// load module if exists
$module = $path->module;
$moduleFile = WW_DIR_MODULES.'/'.$module.'.php';
if (!file_exists($moduleFile))
    trigger_error('Module \''.$module.'\' does not exist', E_USER_ERROR);
include $moduleFile;
