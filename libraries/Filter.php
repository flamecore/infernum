<?php
/**
 * Infernum
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
 * @package  FlameCore\Infernum
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

namespace FlameCore\Infernum;

/**
 * Filter variables and check their type
 *
 * @author   Martin Lantzsch <martin@linux-doku.de>
 */
class Filter
{
    /**
     * Validates the value as a boolean value
     *
     * @param mixed $var The variable to validate
     * @param bool $strict Strict booleans?
     * @return bool|null Returns the boolean value. If $strict is TRUE, FALSE is returned only
     *   for "0", "false", "off", "no", and ""; and NULL is returned for all non-boolean values.
     */
    public static function isBool($var, $strict = false)
    {
        $optionsArg = $strict ? FILTER_NULL_ON_FAILURE : 0;

        return filter_var($var, FILTER_VALIDATE_BOOLEAN, $optionsArg);
    }

    /**
     * Validates the value as integer, optionally from the specified range
     *
     * @param mixed $var The variable to validate
     * @param int $minRange The minimum range (optional)
     * @param int $maxRange The maximum range (optional)
     * @param int $flags One or more of the FILTER_FLAG_* flags; see {@link http://php.net/manual/filter.filters.validate}
     * @return int|bool Returns the filtered data, or FALSE if the filter fails.
     */
    public static function isInt($var, $minRange = null, $maxRange = null, $flags = 0)
    {
        $options = array();
        if (isset($minRange)) {
            $options['min_range'] = $minRange;
        }
        if (isset($maxRange)) {
            $options['max_range'] = $maxRange;
        }

        $optionsArg = array(
            'options' => $options,
            'flags' => $flags
        );

        return filter_var($var, FILTER_VALIDATE_INT, $optionsArg);
    }

    /**
     * Validates the value as float
     *
     * @param mixed $var The variable to validate
     * @param array $decimal The decimal point
     * @param int $flags One or more of the FILTER_FLAG_* flags; see {@link http://php.net/manual/filter.filters.validate}
     * @return float|bool Returns the filtered data, or FALSE if the filter fails.
     */
    public static function isFloat($var, $decimal = null, $flags = 0)
    {
        $options = array();
        if (isset($decimal)) {
            $options['decimal'] = $decimal;
        }

        $optionsArg = array(
            'options' => $options,
            'flags' => $flags
        );

        return filter_var($var, FILTER_VALIDATE_FLOAT, $optionsArg);
    }

    /**
     * Validates the value as an email adress
     *
     * @param mixed $var The variable to validate
     * @return string|bool Returns the filtered data, or FALSE if the filter fails.
     */
    public static function isEmail($var)
    {
        return filter_var($var, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validates the value as URL, optionally with required components
     *
     * @param mixed $var The variable to validate
     * @param int $flags One or more of the FILTER_FLAG_* flags; see {@link http://www.php.net/manual/filter.filters.validate}
     * @return string|bool Returns the filtered data, or FALSE if the filter fails.
     */
    public static function isURL($var, $flags = 0)
    {
        return filter_var($var, FILTER_VALIDATE_URL, $flags);
    }

    /**
     * Validates the value as IP address, optionally only IPv4 or IPv6 or not from private or reserved ranges
     *
     * @param mixed $var The variable to validate
     * @param int $flags One or more of the FILTER_FLAG_* flags; see {@link http://www.php.net/manual/filter.filters.validate}
     * @return string|bool Returns the filtered data, or FALSE if the filter fails.
     */
    public static function isIP($var, $flags = 0)
    {
        return filter_var($var, FILTER_VALIDATE_IP, $flags);
    }

    /**
     * Checks if a variable matches a regex pattern
     *
     * @param mixed $var The variable to check
     * @param string $pattern Match against this pattern
     * @return string|bool Returns the filtered data, or FALSE if the filter fails.
     */
    public static function matchesRegex($var, $pattern = '')
    {
        $optionsArg = array('options' => array('regexp' => $pattern));

        return filter_var($var, FILTER_VALIDATE_REGEXP, $optionsArg);
    }

    /**
     * Removes all characters (from a string) except digits, plus and minus sign
     *
     * @param string $var The variable to sanitize
     * @return string
     */
    public static function sanitizeInt($var)
    {
        return filter_var($var, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Removes all characters (from a string) except digits, plus and minus sign and optionally ., eE
     *
     * @param string $var The variable to sanitize
     * @param int $flags One or more of the FILTER_FLAG_* flags; see {@link http://www.php.net/manual/filter.filters.sanitize}
     * @return string
     */
    public static function sanitizeFloat($var, $flags = 0)
    {
        return filter_var($var, FILTER_SANITIZE_NUMBER_FLOAT, $flags);
    }

    /**
     * Strips tags, optionally strips or encodes special characters (in a string)
     *
     * @param string $var The variable to sanitize
     * @param int $flags One or more of the FILTER_FLAG_* flags; see {@link http://www.php.net/manual/filter.filters.sanitize}
     * @return string
     */
    public static function sanitizeString($var, $flags = 0)
    {
        return filter_var($var, FILTER_SANITIZE_STRING, $flags);
    }

    /**
     * Remove all characters (from a string) except letters, digits and !#$%&'*+-/=?^_`{|}~@.[]
     *
     * @param string $var The variable to sanitize
     * @return string
     */
    public static function sanitizeEmail($var)
    {
        return filter_var($var, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Remove all characters (from a string) except letters, digits and $-_.+!*'(),{}|\\^~[]`<>#%";/?:@&=
     *
     * @param string $var The variable to sanitize
     * @return string
     */
    public static function sanitizeURL($var)
    {
        return filter_var($var, FILTER_SANITIZE_URL);
    }
}
