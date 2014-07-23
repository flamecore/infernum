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

set_error_handler('ww_handle_error');
set_exception_handler('ww_handle_exception');

function ww_handle_error($code, $message, $file, $line) {
    switch ($code) {
        case E_ERROR:
        case E_USER_ERROR:
            throw new ErrorException($message, $code, 2, $file, $line);
            break;

        case E_WARNING:
        case E_USER_WARNING:
            ww_log($message, 1);
            break;
    }
    
    return true;
}

function ww_handle_exception($exception) {
    $severity = is_a($exception, 'ErrorException') ? $exception->getSeverity() : 2;
    ww_log($exception->getMessage(), $severity);

    include WW_ENGINE_PATH.'/includes/errorpage.php';
    exit();
}

function ww_log($message, $severity = 0, $logfile = 'system') {
    if (!isset($GLOBALS['CONFIG']['enable_logging']) || $GLOBALS['CONFIG']['enable_logging'] == false)
        return false;
    
    if ($severity >= 4) {
        $severity_tag = 'ALERT';
    } elseif ($severity == 3) {
        $severity_tag = 'CRITICAL';
    } elseif ($severity == 2) {
        $severity_tag = 'ERROR';
    } elseif ($severity == 1) {
        $severity_tag = 'WARNING';
    } else {
        $severity_tag = 'INFO';
    }

    $logtext = date('Y-m-d H:i:s').' ['.$severity_tag.'] '.$message;
    
    return error_log($logtext.PHP_EOL, 3, WW_ENGINE_PATH.'/logs/'.$logfile.'.log');
}