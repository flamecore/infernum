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
 * @package  FlameCore\Webwork
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

/**
 * Core functions
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */

/**
 * Returns a configuration value
 *
 * @param string $confkey The configuration key
 * @param mixed $default Custom default value (optional)
 * @return mixed
 */
function ww_config($confkey, $default = false)
{
    if (isset($GLOBALS['CONFIG'][$confkey])) {
        return $GLOBALS['CONFIG'][$confkey];
    } else {
        return $default;
    }
}

/**
 * Returns the translation of a string
 *
 * @param string $string The string to translate
 * @param array $vars Variables ('%var%') to replace as array
 * @return string
 */
function t($string, $vars = null)
{
    return International::translate($string, $vars);
}

/**
 * Outputs the translation of a string
 *
 * @param string $string The string to translate
 * @param array $vars Variables ('%var%') to replace as array
 */
function te($string, $vars = null)
{
    echo International::translate($string, $vars);
}
