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
 * Returns the translation of a string
 * @param    string   $string   The string to translate
 * @param    array    $vars     Variables ('%var%') to replace as array
 * @return   string
 */
function t($string, $vars = null) {
    global $t;

    return $t->get($string, $vars);
}

/**
 * Outputs the translation of a string
 * @param    string   $string   The string to translate
 * @param    array    $vars     Variables ('%var%') to replace as array
 * @return   void
 */
function te($string, $vars = null) {
    global $t;

    echo $t->get($string, $vars);
}

/**
 * Generates a URL from a path and query data based on the application URL
 * @param    string   $path    The path of the location to link
 * @param    array    $query   Optional data that is added to the URL as query string.
 *                               For more information, see {@link http://www.php.net/manual/en/function.http-build-query.php}
 * @return   string
 */
function u($path = '', $query = null) {
    $result = WW_ROOT_URL.$path;
    if (isset($query) && is_array($query))
        $result .= '?'.http_build_query($query);
    
    return $result;
}

/**
 * Generates a URL from a page path based on the application URL
 * @param    string   $pagePath   The path of the page to link
 * @param    array    $query      Optional data that is added to the URL as query string.
 *                                  For more information, see {@link http://www.php.net/manual/en/function.http-build-query.php}
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
 * Sends a 404 Not Found error and displays a 'Page not found' message via the 'notfound_body' template
 * @return   void
 */
function showNotFoundError() {
    Http::setHeader('HTTP/1.1 404 Not Found');
    
    $tpl = new Template('notfound_body');
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