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
 * Filter vars and check their type
 *
 * @author  Martin Lantzsch <martin@linux-doku.de>
 */
class Filter {

    /**
     * Checks if a variable is a string
     * @param   mixed  $var  The variable to check
     * @return  bool
     * @access  public
     * @static
     */
    public static function isString($var) {
        return is_string($var);
    }

    /**
     * Checks if a variable is a boolean value
     * @param   mixed   $var     The variable to check
     * @param   bool    $strict  If this is TRUE, FALSE is returned only for "0", "false", "off", "no", and "", and NULL
     *                             is returned for all non-boolean values
     * @return  bool
     * @access  public
     * @static
     */
    public static function isBool($var, $strict = false) {
        if ($strict) {
            $optArg = array('flags' => FILTER_NULL_ON_FAILURE);
        } else {
            $optArg = array();
        }
        return filter_var($var, FITLER_VALIDATE_BOOLEAN, $optArg);
    }

    /**
     * Checks if a variable is an integer
     * @param   mixed  $var      The variable to check
     * @param   array  $options  One or more options as an array,
     *                             see {@link http://www.php.net/manual/en/filter.filters.validate.php}
     * @param   int    $flags    One or more of the FILTER_FLAG_* flags,
     *                             see {@link http://www.php.net/manual/en/filter.filters.validate.php}
     * @return  bool
     * @access  public
     * @static
     */
    public static function isInt($var, $options = array(), $flags = 0) {
        $optArg = array('options' => $options, 'flags' => $flags);
        return filter_var($var, FILTER_VALIDATE_INT, $optArg);
    }

    /**
     * Checks if a variable is a float
     * @param   mixed  $var      The variable to check
     * @param   array  $options  One or more options as an array,
     *                             see {@link http://www.php.net/manual/en/filter.filters.validate.php}
     * @param   int    $flags    One or more of the FILTER_FLAG_* flags,
     *                             see {@link http://www.php.net/manual/en/filter.filters.validate.php}
     * @return  bool
     * @access  public
     * @static
     */
    public static function isFloat($var, $options = array(), $flags = 0) {
        $optArg = array('options' => $options, 'flags' => $flags);
        return filter_var($var, FILTER_VALIDATE_FLOAT, $optArg);
    }

    /**
     * Checks if a variable is a string in the form of an email adress
     * @param   mixed  $var  The variable to check
     * @return  bool
     * @access  public
     * @static
     */
    public static function isEmail($var) {
        return filter_var($var, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Checks if a variable is a string in the form of an URL
     * @param   mixed  $var    The variable to check
     * @param   int    $flags  One or more of the FILTER_FLAG_* flags,
     *                           see {@link http://www.php.net/manual/en/filter.filters.validate.php}
     * @return  bool
     * @access  public
     * @static
     */
    public static function isURL($var, $flags = 0) {
        $optArg = array('flags' => $flags);
        return filter_var($var, FILTER_VALIDATE_URL, $optArg);
    }

    /**
     * Checks if a variable is a string in the form of an IP
     * @param   mixed  $var    The variable to check
     * @param   int    $flags  One or more of the FILTER_FLAG_* flags,
     *                           see {@link http://www.php.net/manual/en/filter.filters.validate.php}
     * @return  bool
     * @access  public
     * @static
     */
    public static function isIP($var, $flags = 0) {
        $optArg = array('flags' => $flags);
        return filter_var($var, FILTER_VALIDATE_IP, $optArg);
    }

    /**
     * Checks if a variable matches a regex pattern
     * @param   mixed   $var      The variable to check
     * @param   string  $pattern  Match against this pattern
     * @return  bool
     * @access  public
     * @static
     */
    public static function matchesRegex($var, $pattern = '') {
        $optArg = array('options' => array('regexp' => $pattern));
        return filter_var($var, FILTER_VALIDATE_REGEXP, $optArg);
    }

    /**
     * Removes all characters (from a string) except digits, plus and minus sign
     * @param   mixed  $var  The variable to sanitize
     * @return  int
     * @access  public
     * @static
     */
    public static function int($var) {
        return filter_var($var, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Removes all characters (from a string) except digits, plus and minus sign and optionally ., eE
     * @param   mixed  $var    The variable to sanitize
     * @param   int    $flags  One or more of the FILTER_FLAG_* flags,
     *                           see {@link http://www.php.net/manual/en/filter.filters.sanitize.php}
     * @return  float
     * @access  public
     * @static
     */
    public static function float($var, $flags = 0) {
        $optArg = array('flags' => $flags);
        return filter_var($var, FILTER_SANITIZE_NUMBER_FLOAT, $optArg);
    }

    /**
     * Strips tags, optionally strips or encodes special characters (in a string)
     * @param   mixed   $var    The variable to sanitize
     * @param   int     $flags  One or more of the FILTER_FLAG_* flags,
     *                            see {@link http://www.php.net/manual/en/filter.filters.sanitize.php}
     * @return  string
     * @access  public
     * @static
     */
    public static function string($var, $flags = 0) {
        $optArg = array('flags' => $flags);
        return filter_var($var, FILTER_SANITIZE_STRING, $optArg);
    }

    /**
     * Remove all characters (from a string) except letters, digits and !#$%&'*+-/=?^_`{|}~@.[]
     * @param   mixed  $var  The variable to sanitize
     * @return  string
     * @access  public
     * @static
     */
    public static function sanitizeEmail($var) {
        return filter_var($var, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Remove all characters (from a string) except letters, digits and $-_.+!*'(),{}|\\^~[]`<>#%";/?:@&=
     * @param   mixed  $var  The variable to sanitize
     * @return  string
     * @access  public
     * @static
     */
    public static function sanitizeURL($var) {
        return filter_var($var, FILTER_SANITIZE_URL);
    }

    /**
     * Formats a number with grouped thousands
     * @param   float   $number          The number to be formatted
     * @param   int     $decimals        Sets the number of decimal points
     * @param   bool    $groupThousands  Enable grouping of thousands
     * @return  string
     * @access  public
     * @static
     */
    public static function formatNumber($number, $decimals = 0, $groupThousands = false) {
        $locale = localeconv();
        return number_format($number, $decimals, $locale['decimal_point'], $groupThousands ? $locale['thousands_sep'] : '');
    }

    /**
     * Formats a number as a currency string
     * @param   float   $number  The number to be formatted
     * @param   string  $format  The money_format() format to use
     * @return  string
     * @access  public
     * @static
     */
    public static function formatCurrency($number, $format = '%i') {
        return money_format($format, $number);
    }

    /**
     * Formats a time/date (UNIX timestamp, MySQL timestamp, string) according to locale settings
     * @param   mixed   $input   The time/date to be formatted
     * @param   string  $format  The strftime() format to use
     * @return  string
     * @access  public
     * @static
     */
    public static function formatTime($input, $format = '%x') {
        if (empty($input)) {
            // empty input string, use current time
            $time = time();
        } elseif ($input instanceof DateTime) {
            $time = $input->getTimestamp();
        } elseif (preg_match('/^\d{14}$/', $input)) {
            // it is MySQL timestamp format of YYYYMMDDHHMMSS
            $time = mktime(substr($input, 8, 2),substr($input, 10, 2),substr($input, 12, 2),
                           substr($input, 4, 2),substr($input, 6, 2),substr($input, 0, 4));
        } elseif (is_numeric($input)) {
            // it is a numeric string, we handle it as timestamp
            $time = (int) $input;
        } else {
            // strtotime should handle it
            $strtotime = strtotime($input);
            if ($strtotime == -1 || $strtotime === false) {
                // strtotime() was not able to parse $input, use current time:
                $time = time();
            }
            $time = $strtotime;
        }
        return strftime($format, $time);
    }

}
