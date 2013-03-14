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
 * Formatting text and values
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Format {
    
    /**
     * Formats a number with grouped thousands
     * @param    float    $number           The number to be formatted
     * @param    int      $decimals         Sets the number of decimal points
     * @param    bool     $groupThousands   Enable grouping of thousands
     * @return   string
     * @access   public
     * @static
     */
    public static function number($number, $decimals = 0, $groupThousands = false) {
        $locale = localeconv();
        
        $decimalPoint = $locale['decimal_point'];
        $thousandsSep = $groupThousands ? $locale['thousands_sep'] : '';
        
        return number_format($number, $decimals, $decimalPoint, $thousandsSep);
    }

    /**
     * Formats a number as a currency string
     * @param    float    $number   The number to be formatted
     * @param    string   $format   The money_format() format to use. Defaults to '%i'.
     * @return   string
     * @access   public
     * @static
     */
    public static function currency($number, $format = '%i') {
        return money_format($format, $number);
    }

    /**
     * Formats the given time or date
     * @param    string   $format   The date() format to use
     * @param    mixed    $input    The time/date to be formatted. Can be DateTime object, UNIX timestamp, MySQL timestamp
     *                                or date/time string. Defaults to the current time.
     * @return   string
     * @access   public
     * @static
     */
    public static function time($format, $input = null) {
        if (!isset($input)) {
            // No input, use current time
            $time = time();
        } elseif ($input instanceof DateTime) {
            $time = $input->getTimestamp();
        } elseif (preg_match('/^\d{14}$/', $input)) {
            // MySQL timestamp format of YYYYMMDDHHMMSS
            $time = mktime(substr($input, 8, 2), substr($input, 10, 2), substr($input, 12, 2),
                           substr($input, 4, 2), substr($input, 6, 2),  substr($input, 0, 4));
        } elseif (is_numeric($input)) {
            // Numeric string, we handle it as timestamp
            $time = (int) $input;
        } else {
            // strtotime() should handle it
            $strtotime = strtotime($input);
            if ($strtotime != -1 && $strtotime !== false) {
                $time = $strtotime;
            } else {
                // strtotime() was not able to parse, use current time
                $time = time();
            }
        }
        
        return date($format, $time);
    }

}
