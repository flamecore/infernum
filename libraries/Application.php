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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * The Application class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
final class Application implements \ArrayAccess
{
    private $container = array();

    private $site;

    private $kernel;

    private $theme = false;

    private static $keys = array(
        'url' => 'string',
        'settings' => 'array',
        'db' => '\FlameCore\Infernum\Database\Base\Connection',
        'cache' => '\FlameCore\Infernum\Cache',
        'session' => '\FlameCore\Infernum\Session',
        'intl' => '\FlameCore\Infernum\International'
    );

    /**
     * Initializes the application.
     *
     * @param \FlameCore\Infernum\Site $site The site context
     * @param \FlameCore\Infernum\Kernel $kernel The kernel
     * @throws \UnexpectedValueException
     */
    public function __construct(Site $site, Kernel $kernel)
    {
        $this->site = $site;
        $this->kernel = $kernel;

        // At first we have to load the settings
        $this['settings'] = $kernel->cache($site->getName().'/settings', [$site, 'loadSettings']);

        // Set web URL
        $url = strtr($this['settings']['web']['url'], ['%domain%' => $kernel['domain']]);
        $this['url'] = rtrim($url, '/');

        // Now we can load our database driver
        $driver = $this['settings']['database']['driver'];
        $host = $this['settings']['database']['host'];
        $user = $this['settings']['database']['user'];
        $password = $this['settings']['database']['password'];
        $database = $this['settings']['database']['database'];
        $prefix = $this['settings']['database']['prefix'];

        $this['db'] = Database\Connection::create($driver, $host, $user, $password, $database, $prefix);

        // Set default timezone
        date_default_timezone_set($this['settings']['site']['timezone']);

        // Open cache instance if enabled
        if ($this->isCacheEnabled())
            $this['cache'] = new Cache($this->getCachePath('data'));
    }

    /**
     * Returns the name of the theme in use.
     *
     * @return string
     * @api
     */
    public function getTheme()
    {
        return $this->theme ? $this->theme : $this->setting('web.theme', 'default');
    }

    /**
     * Overrides the theme.
     *
     * @param string $theme The theme to use
     * @api
     */
    public function setTheme($theme)
    {
        $this->theme = (string) $theme;
    }

    /**
     * Resets the theme.
     *
     * @return void
     * @api
     */
    public function resetTheme()
    {
        $this->theme = false;
    }

    /**
     * Returns the value of a setting.
     *
     * @param string $address The settings address in the form `<section>[:<keyname>]`
     * @param mixed $default Custom default value (optional)
     * @return mixed
     * @api
     */
    public function setting($address, $default = false)
    {
        $addrpart = explode('.', $address, 2);

        $section = $addrpart[0];
        $name = isset($addrpart[1]) ? $addrpart[1] : null;

        if (isset($name)) {
            return isset($this['settings'][$section][$name]) ? $this['settings'][$section][$name] : $default;
        } else {
            return isset($this['settings'][$section]) ? $this['settings'][$section] : $default;
        }
    }

    /**
     * Generates a URL to a path based on the application URL.
     *
     * @param string $path The relative path of the location
     * @param string $query Optional query string that is added to the URL
     * @return string
     * @api
     */
    public function makeURL($path = '', $query = null)
    {
        $result = $this['url'].'/'.$path;

        if (isset($query))
            $result .= '?'.$query;

        return $result;
    }

    /**
     * Generates a URL to a module page.
     *
     * @param string $pagePath The path of the module page
     * @param string $query Optional query string that is added to the URL
     * @return string
     * @api
     */
    public function makePageURL($pagePath, $query = null)
    {
        if ($this->setting('web.url_rewrite')) {
            $result = $this['url'].'/'.$pagePath;

            if (isset($query))
                $result .= '?'.$query;
        } else {
            $result = $this['url'].'/?p='.$pagePath;

            if (isset($query))
                $result .= '&'.$query;
        }

        return $result;
    }

    /**
     * Generates a URL to a file.
     *
     * @param string $filename The name of the file (appended to path)
     * @param string $module Use file URL of this module. If not set, use global file URL.
     * @return string
     * @api
     */
    public function makeFileUrl($filename, $module = null)
    {
        if (isset($module)) {
            return $this['url'].'/modules/'.$module.'/public/'.$filename;
        } else {
            return $this['url'].'/themes/'.$this->getTheme().'/public/'.$filename;
        }
    }

    /**
     * Reads data from cache. The $callback is used to generate the data if missing or expired.
     *
     * @param callable $callback The callback function that returns the data to store
     * @return mixed
     * @api
     */
    public function cache($name, $callback, $lifetime = null)
    {
        if (!is_callable($callback))
            throw new \InvalidArgumentException(sprintf('Invalid callback given for cache file "%s".', $name));

        if (isset($this['cache'])) {
            $data = $this['cache']->get($name);
            if (isset($data)) {
                // We were able to retrieve data
                return $data;
            } else {
                // No data, so we use the given data callback and store the value
                $data = $callback();
                $this['cache']->set($name, $data, isset($lifetime) ? (int) $lifetime : $this->kernel->config('cache_lifetime', 0));
                return $data;
            }
        } else {
            // Caching is disabled, so we use the data callback directly
            return $callback();
        }
    }

    /**
     * Gets the real cookie name based on this context.
     *
     * @param string $name The generic cookie name
     * @return string
     * @api
     */
    public function getCookieName($name)
    {
        return $this->setting('cookie.name_prefix').$name;
    }

    /**
     * Creates a Cookie object based on this context.
     *
     * @param string $name The name of the cookie
     * @param string $value The value of the cookie
     * @param int|string|\DateTime $expire The time the cookie expires
     * @param string $path The path on the server in which the cookie will be available on (Default: settings value)
     * @param string $domain The domain that the cookie is available to (Default: settings value)
     * @param bool $secure Whether the cookie should only be transmitted over a secure HTTPS connection from the client (Default: FALSE)
     * @param bool $httpOnly Whether the cookie will be made accessible only through the HTTP protocol (Default: TRUE)
     * @return \Symfony\Component\HttpFoundation\Cookie
     * @api
     */
    public function createCookie($name, $value = null, $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        $path = $path ?: $this->setting('cookie.path');
        $domain = $domain ?: $this->setting('cookie.domain');

        return new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Returns whether caching is enabled.
     *
     * @return bool
     * @api
     */
    public function isCacheEnabled()
    {
        return $this->kernel->config('enable_caching');
    }

    /**
     * Returns whether debug mode is enabled.
     *
     * @return bool
     * @api
     */
    public function isDebugModeEnabled()
    {
        return $this->kernel->config('enable_debugmode');
    }

    /**
     * Gets the loaded module.
     *
     * @return string Returns the name of the loaded module or FALSE if no module is loaded yet.
     * @api
     */
    public function getLoadedModule()
    {
        return $this->kernel->getLoadedModule();
    }

    /**
     * Gets the path to the cache directory. The directory is created, if it does not exist on the filesystem.
     *
     * @param string $subpath A sub path insiside base cache path (optional)
     * @return string Returns the full cache path.
     * @api
     */
    public function getCachePath($subpath = null)
    {
        $completeSubpath = $this->site->getName();

        if (isset($subpath))
            $completeSubpath .= '/'.$subpath;

        return $this->kernel->getCachePath($completeSubpath);
    }

    /**
     * Gets the template path.
     *
     * @param string $module Use template path of this module. If not set, use global template path.
     * @return string Returns the full cache path.
     * @api
     */
    public function getTemplatePath($module = null)
    {
        if (isset($module)) {
            return $this->kernel->getModulePath($module).'/templates';
        } else {
            return $this->kernel['path'].'/themes/'.$this->getTheme().'/templates';
        }
    }

    /**
     * Finalizes the response.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response The response
     * @internal
     */
    public function finalize(Response &$response)
    {
        if (isset($this['session'])) {
            $name = $this->getCookieName('session');

            if ($this['session']->isActive()) {
                $value = $this['session']->getID();
                $expire = $this['session']->getExpire();

                $cookie = $this->createCookie($name, $value, $expire);
                $response->headers->setCookie($cookie);
            } else {
                $response->headers->clearCookie($name);
            }
        }
    }

    /**
     * Returns the value with specified key.
     *
     * @param string $offset The name of the key
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Returns whether or not a key exists.
     *
     * @param string $offset The name of the key
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Assigns a value to the specified key.
     *
     * @param string $offset The name of the key
     * @param mixed $value The value to assign
     * @throws \InvalidArgumentException if a key with empty name should be set or if the value for a given internal key is invalid.
     * @throws \LogicException if an internal key should be overridden, which is not allowed.
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset))
            throw new \InvalidArgumentException('Cannot set key with empty name in application storage.');

        if (isset(self::$keys[$offset])) {
            if (isset($this->container[$offset]))
                throw new \LogicException(sprintf('Cannot override internal key "%s" in application storage.', $offset));

            $type = self::$keys[$offset];
            if ($type[0] == '\\') {
                $className = substr($type, 1);
                if (!$value instanceof $className)
                    throw new \InvalidArgumentException(sprintf('Value for internal key "%s" in application storage must be an instance of %s class, but %s given.', $offset, $className, is_object($value) ? get_class($value) : gettype($value)));
            }
        }

        $this->container[$offset] = $value;
    }

    /**
     * Unsets the specified key.
     *
     * @param string $offset The name of the key
     * @throws \LogicException if the given key is an internal key, which cannot be unset.
     */
    public function offsetUnset($offset)
    {
        if (isset(self::$keys[$offset]))
            throw new \LogicException(sprintf('Cannot unset internal key "%s" in application storage.', $offset));

        unset($this->container[$offset]);
    }
}
