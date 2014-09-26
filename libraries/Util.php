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

namespace FlameCore\Webwork;

/**
 * Common utilities
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Util
{
    /**
     * Generates a URL to a path based on the application URL
     *
     * @param string $path The relative path of the location
     * @param string $query Optional query string that is added to the URL
     * @return string
     */
    public static function makeURL($path = '', $query = null)
    {
        $root_url = System::setting('Web:Url');

        $result = $root_url.'/'.$path;

        if (isset($query))
            $result .= '?'.$query;

        return $result;
    }

    /**
     * Generates a URL to a module page
     *
     * @param string $pagePath The path of the module page
     * @param string $query Optional query string that is added to the URL
     * @return string
     */
    public static function makePageURL($pagePath, $query = null)
    {
        $root_url = System::setting('Web:Url');

        if (System::setting('Web:UrlRewrite')) {
            $result = $root_url.'/'.$pagePath;

            if (isset($query))
                $result .= '?'.$query;
        } else {
            $result = $root_url.'/?p='.$pagePath;

            if (isset($query))
                $result .= '&'.$query;
        }

        return $result;
    }

    /**
     * Generates a URL to a theme file
     *
     * @param string $filename The name of the file (appended to path)
     * @param string $module Use module theme path instead of global theme path
     * @param string $theme Use this specified theme
     * @return string
     */
    public static function makeThemeFileURL($filename, $module = null, $theme = null)
    {
        $rooturl = System::setting('Web:Url');

        if (!isset($theme))
            $theme = View::getTheme();

        if (isset($module)) {
            $path = $rooturl.'/websites/'.WW_SITE_NAME.'/modules/'.$module;
        } else {
            $path = $rooturl;
        }

        return "{$path}/themes/{$theme}/{$filename}";
    }

    /**
     * Gets the value of an HTTP cookie.
     *
     * @param string $name The name of the cookie. The prefix is prepended automatically.
     * @return string|bool Returns the value of an HTTP cookie. Returns FALSE if the cookie is not set.
     */
    public static function getCookie($name)
    {
        $name_prefix = System::setting('Cookie:NamePrefix');
        $name = $name_prefix.$name;

        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        } else {
            return false;
        }
    }

    /**
     * Defines a cookie to be sent along with the rest of the HTTP headers.
     *
     * @param string $name The name of the cookie
     * @param string|array $value The value of the cookie. Can be a string or an array. If it is an array, all of its
     *   elements will be set all cookies with the name `<name>[<element key>]` and with the value of the array element.
     * @param mixed $expire The time the cookie expires. There are several input possibilities:
     *   * 0 (zero) = Cookie expires at the end of the session (default)
     *   * UNIX timestamp, DateTime object or time/date string
     * @return bool
     */
    public static function setCookie($name, $value, $expire = 0)
    {
        if (headers_sent())
            return false;

        $expire = Util::toTimestamp($expire);

        if (is_array($value)) {
            foreach ($value as $element_key => $element_value)
                self::setCookie($name.'['.$element_key.']', $element_value, $expire);

            return true;
        } else {
            $name_prefix = System::setting('Cookie:NamePrefix');
            $name = $name_prefix.$name;

            $path   = System::setting('Cookie:Path');
            $domain = System::setting('Cookie:Domain');

            return setcookie($name, $value, $expire, $path, $domain);
        }
    }

    /**
     * Forces the deletion of a cookie by setting it to expire
     *
     * @param string $name The name of the cookie
     * @return bool
     */
    public static function deleteCookie($name)
    {
        return self::setCookie($name, '', time()-3600);
    }

    /**
     * Lists the languages that the browser accepts by parsing the 'Accept-Language' header.
     *
     * @return array|bool Returns an array on success or FALSE if no or an invalid header was sent.
     */
    public static function getBrowserLanguages()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $languages = array();

            // Break up the string into pieces (languages and q-factors)
            $pattern = '/(?P<locale>[a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(?P<q>1|0\.[0-9]+))?/i';
            $matched = preg_match_all($pattern, $_SERVER['HTTP_ACCEPT_LANGUAGE'], $items, PREG_SET_ORDER);

            if ($matched != false) {
                // Create list of accepted languages with their q-factor (omitted q-factor = 1)
                foreach ($items as $item)
                    $languages[$item['locale']] = !empty($item['q']) ? (float) $item['q'] : 1.0;

                // Sort the list based on q-factor
                arsort($languages, SORT_NUMERIC);

                return array_keys($languages);
            }
        }

        return false;
    }

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
            if (fnmatch($pattern, $value))
                return true;
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

    /**
     * Parses a Webwork settings file.
     *
     * @param string $filename The filename of the YAML file being parsed
     * @return array Returns a multidimensional array, with the section names and settings included.
     */
    public static function parseSettings($filename)
    {
        if (!is_readable($filename))
            trigger_error('File "'.$filename.'" does not exist or is not readable', E_USER_ERROR);

        return \Symfony\Component\Yaml\Yaml::parse($filename);
    }
}
