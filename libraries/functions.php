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
 * Core functions library
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */

/**
 * Loads function libraries with a given name from multiple sources (module dir, site dir, shared dir)
 * @param    string   $name        The name of the library
 * @param    bool     $exclusive   Stop searching for more libraries when the first one is found. Defaults to FALSE.
 * @return   void
 */
function library($name, $exclusive = false) {
    if ($exclusive) {
        $file = WebworkLoader::find($name, 'libraries/*.php');
        if ($file) {
            include_once $file;
            return;
        }
    } else {
        $files = WebworkLoader::find($name, 'libraries/*.php', true);
        if ($files) {
            foreach ($files as $file)
                include_once $file;
            return;
        }
    }

    throw new WebworkException('Library "'.$name.'" not found.', 'system.library_not_found', E_USER_ERROR);
}

/**
 * Returns the translation of a string
 * @param    string   $string   The string to translate
 * @param    array    $vars     Variables ('%var%') to replace as array
 * @return   string
 */
function t($string, $vars = null) {
    return International::$t->get($string, $vars);
}

/**
 * Outputs the translation of a string
 * @param    string   $string   The string to translate
 * @param    array    $vars     Variables ('%var%') to replace as array
 * @return   void
 */
function te($string, $vars = null) {
    echo International::$t->get($string, $vars);
}

/**
 * Generates a URL to a relative path based on the application URL
 * @param    string   $path    The relative path of the location
 * @param    array    $query   Optional data that is added to the URL as query string.
 *                               For more information see {@link http://www.php.net/http_build_query}
 * @return   string
 */
function u($path = '', $query = null) {
    $result = WW_ROOT_URL.$path;
    if (isset($query) && is_array($query))
        $result .= '?'.http_build_query($query);
    
    return $result;
}

/**
 * Generates a URL to a module page by path
 * @param    string   $pagePath   The path of the module page
 * @param    array    $query      Optional data that is added to the URL as query string.
 *                                  For more information see {@link http://www.php.net/http_build_query}
 * @return   string
 */
function page($pagePath, $query = null) {
    if (System::$settings['core']['url_rewrite']) {
        $result = WW_ROOT_URL.'/'.$pagePath;
        if (isset($query) && is_array($query))
            $result .= '?'.http_build_query($query);
    } else {
        $result = WW_ROOT_URL.'/?p='.$pagePath;
        if (isset($query) && is_array($query))
            $result .= '&'.http_build_query($query);
    }

    return $result;
}

/**
 * Generates a URL to a theme file
 * @param    string   $filename   The name of the file (appended to path)
 * @param    string   $module     Use module theme path instead of global theme path
 * @param    string   $theme      Use this specified theme
 * @return   string
 */
function theme($filename, $module = null, $theme = null) {
	if (!isset($theme))
		$theme = System::$settings['core']['theme'];
	
	if (isset($module)) {
		$path = WW_ROOT_URL.'/sites/'.WW_SITE_NAME.'/modules/'.$module;
	} else {
		$path = WW_ROOT_URL;
	}
	
    return "{$path}/themes/{$theme}/{$filename}";
}

/**
 * Displays a message via the 'message_body' template
 * @param    string   $message   The text of the message to show. In the template, this value can be retrieved via
 *                                 the {$message} variable.
 * @param    string   $type      The type of the message, should be either 'info', 'success', 'warning' or 'error'.
 *                                 In the template, this value can be retrieved via the {$type} variable.
 * @return   void
 */
function showMessage($message, $type = 'info') {
    $tpl = new Template('message_body');
    $tpl->set('message', $message);
    $tpl->set('type', $type);
    $tpl->render();
    
    exit();
}

/**
 * Sends an HTTP error and displays a message via template
 * @param    int    $code   The HTTP error code. Possible values are:
 *                            * 404      Sends '404 Not Found' error, displays '404_body' template (default)
 *                            * 403      Sends '403 Forbidden' error, displays '403_body' template
 * @return   void
 */
function showError($code = 404) {
    switch ($code) {
        case 404:
        default:
            $errorstring = '404 Not Found';
            $errortpl = '404_body';
            break;
        case 403:
            $errorstring = '403 Forbidden';
            $errortpl = '403_body';
            break;
    }
    
    Http::setHeader('HTTP/1.1 '.$errorstring);
    
    $tpl = new Template($errortpl);
    $tpl->render();
    
    exit();
}

/**
 * Checks if the path matches the given list of patterns
 * @param    string   $patternList   List of fnmatch() patterns separated by semicolons (;)
 * @return   bool
 */
function isCurrentPath($patternList) {
    global $path;

    $patterns = explode(';', $patternList);

    foreach ($patterns as $pattern) {
        if ($pattern[0] != '/')
            continue;

        if (fnmatch($pattern, '/'.$path))
            return true;
    }

    return false;
}