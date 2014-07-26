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
 * Wrapper for several HTTP features
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Http {

    /**
     * Returns the value of an HTTP cookie. Returns FALSE if the cookie is not set.
     * @param    string   $name   The name of the cookie. The prefix is prepended automatically.
     * @return   mixed
     * @access   public
     * @static
     */
    public static function getCookie($name) {
        $name_prefix = System::setting('Cookie:NamePrefix');
        $name = $name_prefix.$name;
        
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        } else {
            return false;
        }
    }
    
    /**
     * Defines a cookie to be sent along with the rest of the HTTP headers
     * @param    string   $name       The name of the cookie
     * @param    mixed    $value      The value of the cookie. Can be a string or an array. If it is an array, all of its
     *                                  elements will be set all cookies with the name '<name>[<element key>]' and with the
     *                                  value of the array element.
     * @param    mixed    $expire     The time the cookie expires. There are several input possibilities:
     *                                  * 0 (zero) = Cookie expires at the end of the session (default)
     *                                  * UNIX timestamp, DateTime object or time/date string
     * @return   bool
     * @access   public
     * @static
     */
    public static function setCookie($name, $value, $expire = 0) {
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
     * @param    string   $name   The name of the cookie
     * @return   bool
     * @access   public
     * @static
     */
    public static function deleteCookie($name) {
        return self::setCookie($name, '', time()-3600);
    }

    /**
     * Generates a header redirection
     * @param    string   $url        The URL where the redirection goes to
     * @param    int      $response   Forces the HTTP response code to the specified value (Default: 302)
     * @return   bool
     * @access   public
     * @static
     */
    public static function redirect($url, $response = 302) {
        return header('Location: '.$url, true, $response);
    }
    
    /**
     * Lists the languages that the browser accepts by parsing the 'Accept-Language' header. Returns an array on success
     *   or FALSE if no or an invalid header was sent.
     * @return   array
     * @access   public
     * @static
     */
    public static function getBrowserLanguages() {
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

}
