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

error_reporting(0);

set_error_handler('ww_handle_error');
set_exception_handler('ww_handle_exception');

function ww_handle_error($err_code, $err_message, $err_file, $err_line) {
    switch ($err_code) {
        case E_USER_ERROR:
            ww_log($err_message, 2);
            
            include WW_ENGINE_PATH.'/includes/errorpage.php';
            exit();
            break;

        case E_WARNING:
        case E_USER_WARNING:
            ww_log($err_message, 1);
            break;

        default:
            return false;
            break;
    }
    
    return true;
}

function ww_handle_exception($exception) {
    ww_handle_error(E_USER_ERROR, $exception->getMessage(), $exception->getFile(), $exception->getLine());
}

function ww_log($message, $severity = 0, $logfile = 'system') {
    if (!isset($GLOBALS['CONFIG']['enable_logging']) || $GLOBALS['CONFIG']['enable_logging'] == false)
        return false;
    
    if ($severity >= 2) {
        $severity_tag = 'ERROR';
    } elseif ($severity == 1) {
        $severity_tag = 'WARNING';
    } else {
        $severity_tag = 'INFO';
    }

    $logtext = date('Y-m-d H:i:s').' ['.$severity_tag.'] '.$message;
    
    return error_log($logtext.PHP_EOL, 3, WW_ENGINE_PATH.'/logs/'.$logfile.'.log');
}