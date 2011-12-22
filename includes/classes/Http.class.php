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
 * Wrapper for several HTTP features
 *
 * @author  Christian Neff <christian.neff@gmail.com>
 */
class Http {

    /**
     * Gets a HTTP GET variable. Returns FALSE if the variable is not set.
     * @param   string  $name       The name of the variable
     * @param   bool    $valueType  The type in which the value should be returned. Possible values are: 'string', 'int'.
     *                                Defaults to 'string'.
     * @return  mixed
     * @access  public
     * @static
     */
    public static function getGetVar($name, $valueType = 'string') {
        if (isset($_GET[$name])) {
            $val =& $_GET[$name];
            
            if ($valueType == 'string') {
                return $val;
            } elseif ($valueType == 'int') {
                return (int) $val;
            }
        }

        return false;
    }

    /**
     * Gets a HTTP POST variable. Returns FALSE if the variable is not set.
     * @param   string  $name       The name of the variable
     * @param   bool    $valueType  The type in which the value should be returned. Possible values are: 'string', 'int'.
     *                                Defaults to 'string'.
     * @return  mixed
     * @access  public
     * @static
     */
    public static function getPostVar($name, $valueType = 'string') {
        if (isset($_POST[$name])) {
            $val =& $_POST[$name];
            
            if ($valueType == 'string') {
                return $val;
            } elseif ($valueType == 'int') {
                return (int) $val;
            }
        }

        return false;
    }
    
    /**
     * Defines a cookie to be sent along with the rest of the HTTP headers
     * @param   string  $name      The name of the cookie
     * @param   mixed   $value     The value of the cookie. Can be a string or an array. If it is an array, all of its
     *                               elements will be set all cookies with the name '<name>[<element key>]' and with the
     *                               value of the array element.
     * @param   mixed   $expire    The time the cookie expires. There are several input possibilities:
     *                               * 0 (zero)     Cookie expires at the end of the session (default)
     *                               * <timestamp>  Cookie expires at given timestamp (moment)
     *                               * '+Xm'        Cookie expires in X minutes (period)
     *                               * '+Xh'        Cookie expires in X hours (period)
     *                               * '+Xd'        Cookie expires in X days (period)
     * @param   string  $path      The path on the server in which the cookie will be available on. Defaults to the cookie
     *                               path defined in the configuration.
     * @param   string  $domain    The domain that the cookie is available to. Defaults to the cookie domain defined in
     *                               the configuration.
     * @param   bool    $secure    Indicates that the cookie should only be transmitted over a secure HTTPS connection from
     *                               the client. Defaults to FALSE.
     * @param   bool    $httpOnly  When TRUE the cookie will be made accessible only through the HTTP protocol. Defaults
     *                               to FALSE.
     * @return  bool
     * @access  public
     * @static
     */
    public static function setCookie($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = false) {
        $expire = self::_parseExpireTime($expire);
        if (!isset($path))
            $path = Settings::get('core', 'cookie_path');
        if (!isset($domain))
            $domain = Settings::get('core', 'cookie_domain');

        if (is_array($value)) {
            foreach ($value as $elementKey => $elementValue) {
                self::setCookie($name.'['.$elementKey.']', $elementValue, $expire, $path, $domain, $secure, $httpOnly);
            }
            
            return true;
        } else {
            return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        }
    }
    
    /**
     * Deletes one or multiple cookies
     * @param   string  $name     The name of the cookie
     * @param   bool    $isArray  If it is an array cookie, this parameter indicates that all sub cookies should be
     *                              deleted as well.
     * @return  bool
     * @access  public
     * @static
     */
    public static function deleteCookie($name, $isArray = false) {
        if ($isArray) {
            // find all cookies to delete
            foreach ($_COOKIE as $cookieName => $cookieValue) {
                if (fnmatch($name.'[*]', $cookieName)) {
                    self::setCookie($cookieName, '', time()-3600);
                }
            }
            
            return true;
        } else {
            return self::setCookie($name, '', time()-3600);
        }
    }

    /**
     * Sends a raw HTTP header
     * @param   string  $directive  The header directive to set or a HTTP status code string
     * @param   string  $value      The value of the header directive you want to set. Not neccessary if you set a HTTP
     *                                status status code string.
     * @param   bool    $replace    Indicates whether the header should replace a previous similar header, or add a second
     *                                header of the same type. Defaults to TRUE.
     * @param   int     $respCode   Forces the HTTP response code to the specified value. Optional.
     * @return  bool
     * @access  public
     * @static
     */
    public static function setHeader($directive, $value = null, $replace = true, $respCode = null) {
        if (!headers_sent()) {
            if (isset($value)) {
                header($directive.': '.$value, $replace, $respCode);
            } else {
                header($directive, $replace, $respCode);
            }

            return true;
        }

        return false;
    }

    /**
     * Generates a header redirection
     * @param   string  $url       The URL where the redirection goes to
     * @param   int     $respCode  Forces the HTTP response code to the specified value. Defaults to 302.
     * @return  bool
     * @access  public
     * @static
     */
    public static function redirect($url, $respCode = 302) {
        return self::setHeader('Location', $url, true, $respCode);
    }

    /**
     * Parses a given expire time
     * @param   mixed    $expireTime  The time the cookie expires. There are several input possibilities:
     *                                  * 0 (zero)     Cookie expires at the end of the session (default)
     *                                  * <timestamp>  Cookie expires at given timestamp (moment)
     *                                  * '+Xm'        Cookie expires in X minutes (period)
     *                                  * '+Xh'        Cookie expires in X hours (period)
     *                                  * '+Xd'        Cookie expires in X days (period)
     * @return  int
     * @access  private
     * @static
     */
    private static function _parseExpireTime($expireTime) {
        if (is_int($expireTime)) {
            return $expireTime;
        } elseif (preg_match('/\+([0-9]+)(m|h|d)/i', $expireTime, $matches)) {
            $factor = (int) $matches[1];
            $timeUnit = $matches[2];

            if ($timeUnit == 'm') {
                $seconds = $factor * 60;
            } elseif ($timeUnit == 'h') {
                $seconds = $factor * 60 * 60;
            } elseif ($timeUnit == 'd') {
                $seconds = $factor * 60 * 60 * 24;
            }

            return time() + $seconds;
        } else {
            return 0;
        }
    }

}
