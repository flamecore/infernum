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
     * Shortens a text string to the given length. The truncated text part is replaced by an ellipsis.
     * @param    string   $string        The text string to shorten 
     * @param    int      $length        Determines how many characters to shorten to (Default = 80)
     * @param    string   $ellipsis      Text string that replaces truncated text part. (Default = '...')
     *                                     Note: Length is included in shortening length setting.
     * @param    bool     $break_words   Break words when truncating? (Default = FALSE)
     *                                     Note: FALSE truncates the text only at word boundaries.
     * @param    bool     $middle        Truncate in the middle of the string? (Default = FALSE)
     *                                     Note: With this option activated, word boundaries are ignored.
     * @return   string
     * @access   public
     * @static
     */
    public static function shorten($string, $length = 80, $ellipsis = '...', $break_words = false, $middle = false) {
        if ($length == 0)
            return '';

        if (isset($string[$length])) {
            $length -= min($length, strlen($ellipsis));

            if (!$break_words && !$middle)
                $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));

            if (!$middle)
                return substr($string, 0, $length).$ellipsis;

            return substr($string, 0, $length / 2).$ellipsis.substr($string, - $length / 2);
        }

        return $string;
    }
    
    /**
     * Formats a number with grouped thousands
     * @param    float    $number            The number to be formatted
     * @param    int      $decimals          Sets the number of decimal points (Default = 0)
     * @param    bool     $group_thousands   Enable grouping of thousands (Default = FALSE)
     * @return   string
     * @access   public
     * @static
     */
    public static function number($number, $decimals = 0, $group_thousands = false) {
        $locale = localeconv();
        
        $decimal_sep = $locale['decimal_point'];
        $thousands_sep = $group_thousands ? $locale['thousands_sep'] : '';
        
        return number_format($number, $decimals, $decimal_sep, $thousands_sep);
    }

    /**
     * Formats a number as a monetary string
     * @param    float    $number   The number to be formatted
     * @param    string   $format   The money_format() format to use (Default = '%i')
     * @return   string
     * @access   public
     * @static
     */
    public static function money($number, $format = '%i') {
        return money_format($format, $number);
    }

    /**
     * Formats the given time or date
     * @param    string   $format   The date() format to use
     * @param    mixed    $input    Time/Date to be formatted. Can be UNIX timestamp, DateTime object or time/date string.
     *                                When omitted, the current time is used.
     * @return   string
     * @access   public
     * @static
     */
    public static function time($format, $input = null) {
        if (isset($input)) {
            $time = Util::toTimestamp($input);
        } else {
            $time = time();
        }
        
        return date($format, $time);
    }

}
