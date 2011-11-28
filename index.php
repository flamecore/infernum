<?php
/**
 * HadesLite
 * Copyright (C) 2011 Hades Project
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
 * @package     HadesLite
 * @version     0.1-dev
 * @link        http://hades.iceflame.net
 * @license     ISC License (http://www.opensource.org/licenses/ISC)
 */

/**
 * HadesLite Core
 *
 * @author  Christian Neff <christian.neff@gmail.com>
 */

define('HLFW_DIR_ROOT', __DIR__);
define('HLFW_DIR_INCLUDES', HLFW_DIR_ROOT.'/includes');
define('HLFW_DIR_CONTROLLERS', HLFW_DIR_ROOT.'/controllers');
define('HLFW_DIR_SETTINGS', HLFW_DIR_ROOT.'/settings');
define('HLFW_DIR_TEMP', HLFW_DIR_ROOT.'/temp');
define('HLFW_DIR_CACHE', HLFW_DIR_TEMP.'/cache');
define('HLFW_DIR_ASSETS', HLFW_DIR_ROOT.'/assets');
define('HLFW_DIR_THEMES', HLFW_DIR_ROOT.'/themes');

// load bootstrap
require_once HLFW_DIR_INCLUDES.'/bootstrap.php';

// set page title
Template::$pageTitle = Settings::get('core', 'site_name');

// get module from params
$path = new Path($_GET['p']);

// load controller if exists
$controller = $path->controller;
$controllerFile = HADES_DIR_CONTROLLERS.'/'.$controller.'.php';
if (!file_exists($controllerFile))
    trigger_error('Controller \''.$controller.'\' does not exist', E_USER_ERROR);
include_once $controllerFile;

// create controller instance
$controllerClass = 'controller_'.$controller;
$instance = new $controllerClass;

// determine action method
$action = $path->action;
if (!method_exists($instance, $action))
    trigger_error('Action \''.$action.'\' does not exist in controller \''.$controller.'\'', E_USER_ERROR);

// determine params
$params = $path->params;

// call the action method
if (is_array($params) && !empty($params)) {
    call_user_func_array(array($instance, $action), $params);
} else {
    call_user_func(array($instance, $action));
}
