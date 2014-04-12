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
 * Returns the value of a setting
 * @param    string   $address   The settings address in the form "<section>[:<keyname>]"
 * @param    mixed    $default   Custom default value (optional)
 * @return   mixed
 */
function ww_setting($address, $default = false) {
    if (preg_match('/^(\w+)(:(\w+))$/', $address, $addrpart)) {
        $section = $addrpart[1];
        $keyname = isset($addrpart[2]) ? $addrpart[3] : null;
    } else {
        trigger_error('Invalid settings address "'.$address.'" given');
    }

    return System::setting($section, $keyname, $default);
}

/**
 * Creates a new database driver instance
 * @param    string   $driver     The database driver to use
 * @param    string   $host       The database server host, mostly 'localhost'
 * @param    string   $user       The username for authenticating at the database server
 * @param    string   $password   The password for authenticating at the database server
 * @param    string   $database   The name of the database
 * @param    string   $prefix     The prefix of the database tables
 * @return   Database_Base_Driver
 */
function ww_open_db($driver, $host, $user, $password, $database, $prefix) {
    if (!is_string($driver) || empty($driver))
        trigger_error('Database driver name invalid', E_USER_ERROR);

    $driver_class = "Database_{$driver}_Driver";

    if (!class_exists($driver_class))
        trigger_error('Database driver "'.$driver.'" not available', E_USER_ERROR);

    return new $driver_class($host, $user, $password, $database, $prefix);
}

/**
 * Parses a Webwork settings file. Returns a multidimensional array, with the section names and settings included.
 * @param    string   $filename   The filename of the INI file being parsed.
 * @return   array
 */
function parse_settings($filename) {
    if (!is_readable($filename))
        trigger_error('File "'.$filename.'" does not exist or is not readable', E_USER_ERROR);
    
    $section = 'main'; $settings = array();
	
	$fn_error = function ($message) use ($filename, &$section) {
		trigger_error($filename.' ['.$section.']: '.$message, E_USER_ERROR);
	};
    
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/^(;|#)/', $line)) {
            // Comment
            continue;
        } elseif (preg_match('/^(\w+)\s*=\s*(.+)$/', $line, $part)) {
            // Directive declaration
            list(, $key, $val) = $part;
            $result = json_decode('{"val":'.$val.'}', true);
            if (isset($result)) {
                $settings[$section][$key] = $result['val'];
            } else {
                $errno = json_last_error();
                if ($errno == JSON_ERROR_DEPTH) {
                    $fn_error('Maximum JSON stack depth exceeded');
                } elseif ($errno == JSON_ERROR_STATE_MISMATCH) {
                    $fn_error('Underflow or the modes mismatch');
                } elseif ($errno == JSON_ERROR_SYNTAX) {
                    $fn_error('Malformed value');
                } else {
                    $fn_error('Unknown error in value');
                }
            }
        } elseif (preg_match('/^\[(\w+)\]$/', $line, $part)) {
            // Section declaration
            $section = $part[1];
            $settings[$section] = array();
        } else {
            // Anything else
            $fn_error('Invalid command');
        }
    }
    
    return $settings;
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
 * Generates a URL to a relative path based on the application URL
 * @param    string   $path    The relative path of the location
 * @param    array    $query   Optional data that is added to the URL as query string.
 *                               For more information see {@link http://www.php.net/http_build_query}
 * @return   string
 */
function u($path = '', $query = null) {
    $rooturl = ww_setting('main:url');
    
    $result = $rooturl.$path;
    
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
    $rooturl = ww_setting('main:url');
    
    if (ww_setting('main:url_rewrite')) {
        $result = $rooturl.'/'.$pagePath;
        
        if (isset($query) && is_array($query))
            $result .= '?'.http_build_query($query);
    } else {
        $result = $rooturl.'/?p='.$pagePath;
        
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
    $rooturl = ww_setting('main:url');
    
	if (!isset($theme))
		$theme = ww_setting('main:theme');
	
	if (isset($module)) {
        $path = $rooturl.'/websites/'.WW_SITE_NAME.'/modules/'.$module;
	} else {
        $path = $rooturl;
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
function message($message, $type = 'info') {
    $tpl = new Template('message_body');
    $tpl->set('message', $message);
    $tpl->set('type', $type);
    $tpl->display();
    
    exit();
}

/**
 * Sends an HTTP error and displays a message via template
 * @param    int    $code   The HTTP error code. Possible values are:
 *                            * 404      Sends '404 Not Found' error, displays '404_body' template
 *                            * 403      Sends '403 Forbidden' error, displays '403_body' template
 * @return   void
 */
function error($code) {
    switch ($code) {
        case 404:
            $errorstr = '404 Not Found';
            $errortpl = '404_body';
            break;
        case 403:
            $errorstr = '403 Forbidden';
            $errortpl = '403_body';
            break;
    }
    
    Http::setHeader('HTTP/1.1 '.$errorstr);
    
    $tpl = new Template($errortpl);
    $tpl->display();
    
    exit();
}

/**
 * Checks if the path matches the given list of patterns
 * @param    string   $patternList   List of fnmatch() patterns separated by semicolons (;)
 * @return   bool
 */
function is_current_path($patternList) {
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