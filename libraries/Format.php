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
 * Formatting text and values according to locales
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Format
{
    /**
     * Shortens a text string to the given length. The truncated text part is replaced by an ellipsis.
     *
     * @param string $string The text to shorten
     * @param int $length Determines how many characters to shorten to (Default: 80)
     * @param string $ellipsis Text string that replaces truncated text part (Default: '...').
     *   Note that the length is included in shortening length setting.
     * @param bool $break_words Break words when truncating? (Default: FALSE).
     *   Note that FALSE truncates the text only at word boundaries.
     * @param bool $middle Truncate in the middle of the string (Default: FALSE).
     *   Note that with this option activated, word boundaries are ignored.
     * @return string
     */
    public static function shorten($string, $length = 80, $ellipsis = '...', $break_words = false, $middle = false)
    {
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
     * Formats a number with grouped thousands.
     *
     * @param float $number The number to be formatted
     * @param int $decimals The number of decimal points to display (Default: 0)
     * @param string $decimalPoint The character(s) to use for the decimal point (Default: ".")
     * @param string $thousandSep The character(s) to use for the thousands separator (Default: ",")
     * @return string
     */
    public static function number($number, $decimals = 0, $decimalPoint = '.', $thousandSep = ',')
    {
        return number_format((float) $number, $decimals, $decimalPoint, $thousandSep);
    }

    /**
     * Formats a number as a monetary string.
     *
     * @param float $number The number to be formatted
     * @param string $currency The currency to use
     * @param string $format The money format to use in the form '[$ ]#[.]###.#[..][ $]'
     * @return string
     */
    public static function money($number, $currency, $format)
    {
        if (preg_match('/(\$ ?)*#(.?)###(.)(#+)( ?\$)*/', $format, $parts)) {
            $prefix = str_replace('$', $currency, $parts[1]);
            $thousandSep = $parts[2];
            $decimalPoint = $parts[3];
            $decimals = strlen($parts[4]);
            $suffix = isset($parts[5]) ? str_replace('$', $currency, $parts[5]) : '';
        } else {
            trigger_error(sprintf('Invalid money format "%s" given', $format), E_USER_WARNING);
            return $number;
        }

        return $prefix . number_format($number, $decimals, $decimalPoint, $thousandSep) . $suffix;
    }

    /**
     * Formats the given time/date to a time of day string.
     *
     * @param mixed $input Time/Date to be formatted. Can be UNIX timestamp, DateTime object or time/date string.
     *   When NULL, the current time is used.
     * @param string $format The date() format to use instead of locale specification
     * @return string
     */
    public static function time($input, $format)
    {
        $time = isset($input) ? Util::toTimestamp($input) : time();

        return date($format, $time);
    }
}
