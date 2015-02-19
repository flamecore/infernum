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
 * Common utilities
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Util
{
    /**
     * Checks if the given value matches the list of patterns
     *
     * @param string $value The value to match
     * @param string $list List of fnmatch() patterns separated by commas
     * @return bool
     */
    public static function matchesPatternList($value, $list)
    {
        $patterns = explode(',', $list);

        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Transforms the given input into a timestamp
     *
     * @param mixed $input Time/Date input can be UNIX timestamp, DateTime object or time/date string
     * @return int
     */
    public static function toTimestamp($input)
    {
        if (is_numeric($input)) {
            // Numeric input, we handle it as timestamp
            return (int) $input;
        } elseif ($input instanceof \DateTime) {
            // DateTime object, get timestamp
            return $input->getTimestamp();
        } else {
            // strtotime() should handle it
            $strtotime = strtotime($input);
            if ($strtotime != -1 && $strtotime !== false) {
                return $strtotime;
            } else {
                // strtotime() was not able to parse, use current time
                return time();
            }
        }
    }
}
