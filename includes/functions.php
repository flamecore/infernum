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
 * Common functions
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */

/**
 * Returns the translation of a string
 * @param    string   $string   The string to translate
 * @param    array    $vars     Variables ('%var%') to replace as array
 * @param    bool     $raw      Return value as plain text (not HTML-encoded)? Defaults to FALSE.
 * @return   string
 */
function t($string, $vars = null, $raw = false) {
    $result = Lang::get($string, $vars);
    
    if (!$raw)
        $result = htmlentities($result);
    
    return $result;
}

/**
 * Generates a URL from a path and query data based on the application URL
 * @param    string   $path    The path of the location to link
 * @param    array    $query   Optional data that is added to the URL as query string.
 *                               For more information, see {@link http://www.php.net/manual/en/function.http-build-query.php}
 * @return   string
 */
function makeURL($path, $query = null) {
    $rootURL = Settings::get('core', 'url');
    
    $result = $rootURL.$path;
    if (isset($query) && is_array($query))
        $result .= '?'.http_build_query($query);
    
    return $result;
}

/**
 * Generates a URL from a page path based on the application URL
 * @param    string   $pagePath   The path of the page to link
 * @return   string
 */
function makePageURL($pagePath) {
    $query = array('p' => $pagePath);
    return makeURL('/', $query);
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
    $tpl = new Template('message_body', 'core');
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
    $tpl = new Template('notfound_body', 'core');
    $tpl->render();
    exit();
}