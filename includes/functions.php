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
 * Returns a configuration value
 * @param    string   $confkey   The configuration key
 * @param    mixed    $default   Custom default value (optional)
 * @return   mixed
 */
function ww_config($confkey, $default = false) {
    if (isset($GLOBALS['CONFIG'][$confkey])) {
        return $GLOBALS['CONFIG'][$confkey];
    } else {
        return $default;
    }
}

/**
 * Parses a Webwork settings file. Returns a multidimensional array, with the section names and settings included.
 * @param    string   $filename   The filename of the YAML file being parsed
 * @return   array
 */
function parse_settings($filename) {
    if (!is_readable($filename))
        trigger_error('File "'.$filename.'" does not exist or is not readable', E_USER_ERROR);
    
    return \Symfony\Component\Yaml\Yaml::parse($filename);
}

/**
 * Returns the translation of a string
 * @param    string   $string   The string to translate
 * @param    array    $vars     Variables ('%var%') to replace as array
 * @return   string
 */
function t($string, $vars = null) {
    return International::t()->get($string, $vars);
}

/**
 * Outputs the translation of a string
 * @param    string   $string   The string to translate
 * @param    array    $vars     Variables ('%var%') to replace as array
 * @return   void
 */
function te($string, $vars = null) {
    echo International::t()->get($string, $vars);
}

/**
 * Displays a message via the 'message_body' template
 * @param    string   $message   The text of the message to show. In the template, this value can be retrieved via
 *                                 the {$message} variable.
 * @param    string   $type      The type of the message, should be either 'info', 'success', 'warning' or 'error'.
 *                                 In the template, this value can be retrieved via the {$type} variable.
 * @return   void
 */
function message($message, $type = 'info') {
    $tpl = new Template('message_body');
    $tpl->set('message', $message);
    $tpl->set('type', $type);
    $tpl->display();
    
    exit();
}

/**
 * Sends an HTTP '404 Not Found' error and displays a message
 * @return   void
 */
function not_found_error() {
    Http::setHeader('HTTP/1.1 404 Not Found');
    
    $tpl = new Template('404_body');
    $tpl->display();
    
    exit();
}

/**
 * Sends an HTTP '403 Forbidden' error and displays a message
 * @return   void
 */
function forbidden_error() {
    Http::setHeader('HTTP/1.1 403 Forbidden');
    
    $tpl = new Template('403_body');
    $tpl->display();
    
    exit();
}