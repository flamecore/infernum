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
 * Class for managing the basic core features
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 * @author   Sebastian Wagner <szebi@gmx.at>
 */
class System
{
    /**
     * All loaded settings
     *
     * @var array
     */
    private static $settings = array();

    /**
     * The database driver object
     *
     * @var Database_Connection
     */
    private static $db;

    /**
     * The Session manager object
     *
     * @var Session
     */
    private static $session;

    /**
     * List of mounted modules
     *
     * @var array
     */
    private static $modules = array();

    /**
     * Is the system initialized?
     *
     * @var bool
     */
    private static $initialized = false;

    /**
     * Initializes the system
     *
     * @return void
     */
    public static function startup()
    {
        if (!is_dir(WW_SITE_PATH))
            trigger_error('Directory of site "'.WW_SITE_NAME.'" does not exist', E_USER_ERROR);

        // At first we have to load the settings
        $cache = new Cache('settings');
        self::$settings = $cache->data(function () {
            return Util::parseSettings(WW_SITE_PATH.'/settings.yml');
        });

        // Make sure that the required settings are available and shut down the system otherwise
        if (!isset(self::$settings['Main']) || !isset(self::$settings['Database']))
            trigger_error('Required settings "Main" and/or "Database" not available', E_USER_ERROR);

        // Now we can load our database driver
        $driver = self::$settings['Database']['Driver'];
        $host = self::$settings['Database']['Host'];
        $user = self::$settings['Database']['User'];
        $password = self::$settings['Database']['Password'];
        $database = self::$settings['Database']['Database'];
        $prefix = self::$settings['Database']['Prefix'];

        self::$db = Database\Connection::create($driver, $host, $user, $password, $database, $prefix);

        // All systems are started now and running smoothly
        self::$initialized = true;

        // Start user session
        self::$session = Session::init();
    }

    /**
     * Checks if the sytem has been started
     *
     * @return bool
     */
    public static function isStarted()
    {
        return self::$initialized;
    }

    /**
     * Returns the value of a setting
     *
     * @param string $address The settings address in the form "<section>[:<keyname>]"
     * @param mixed $default Custom default value (optional)
     * @return mixed
     */
    public static function setting($address, $default = false)
    {
        if (!self::isStarted())
            trigger_error('The system is not yet ready', E_USER_ERROR);

        $addrpart = explode(':', $address, 2);

        if (isset($addrpart[1])) {
            return isset(self::$settings[$addrpart[0]][$addrpart[1]]) ? self::$settings[$addrpart[0]][$addrpart[1]] : $default;
        } else {
            return isset(self::$settings[$addrpart[0]]) ? self::$settings[$addrpart[0]] : $default;
        }
    }

    /**
     * Returns the database driver object
     *
     * @return Database_Connection
     */
    public static function db()
    {
        if (!self::isStarted())
            trigger_error('The system is not yet ready', E_USER_ERROR);

        return self::$db;
    }

    /**
     * Returns the Session manager object
     *
     * @return Session
     */
    public static function getSession()
    {
        if (!self::isStarted())
            trigger_error('The system is not yet ready', E_USER_ERROR);

        return self::$session;
    }

    /**
     * Mounts the given module.
     *
     * @param string $name The name of the module to mount
     * @param string $alias Alternative mountpoint (optional)
     */
    public static function mountModule($name, $alias = null)
    {
        $mountpoint = $alias ?: str_replace('_', '-', $name);

        self::$modules[$mountpoint] = $name;
    }

    /**
     * Unmounts the given module.
     *
     * @param string $name The name of the module to unmount
     */
    public static function unmountModule($name)
    {
        $mountpoints = array_keys(self::$modules, $name);

        foreach ($mountpoints as $mountpoint) {
            unset(self::$modules[$mountpoint]);
        }
    }

    /**
     * Lists all mounted modules with their mountpoint.
     *
     * @return array Returns an array in the form `['mount-point' => 'module_name', ...]`
     */
    public static function getMountedModules()
    {
        if (!self::isStarted())
            trigger_error('The system is not yet ready', E_USER_ERROR);

        return self::$modules;
    }

    /**
     * Checks whether a module exists.
     *
     * @param string $module The module name
     * @return bool
     */
    public static function moduleExists($module)
    {
        return is_readable(WW_ENGINE_PATH.'/modules/'.$module.'/controller.php');
    }

    /**
     * Loads a module controller
     *
     * @param string $module The name of the module
     * @param array $arguments The arguments to use
     */
    public static function loadModule($module, $action, Array $arguments = null)
    {
        if (!self::isStarted())
            trigger_error('The system is not yet ready', E_USER_ERROR);

        if (defined('WW_MODULE'))
            trigger_error('A module has already been loaded', E_USER_ERROR);

        if (!self::moduleExists($module))
            return false;

        define('WW_MODULE', $module);
        define('WW_MODULE_PATH', WW_ENGINE_PATH.'/modules/'.$module);

        include_once WW_MODULE_PATH.'/controller.php';

        $controller_class = 'module_'.WW_MODULE;

        if (!class_exists($controller_class) || !is_subclass_of($controller_class, 'Controller'))
            trigger_error('Module "'.$module.'" does not provide a valid controller', E_USER_ERROR);

        $module = new $controller_class();
        $module->run($action, $arguments)->send();
    }

    /**
     * Loads a module controller by given path
     *
     * @param string $path The path of the module page
     */
    public static function loadModuleFromPath($path)
    {
        $path_parts = explode('/', $path);

        if (count($path_parts) > 2) {
            $mount = array_shift($path_parts);
            $action = array_shift($path_parts);
            $arguments = $path_parts;
        } elseif (count($path_parts) == 2) {
            $mount = $path_parts[0];
            $action = $path_parts[1];
            $arguments = null;
        } else {
            $mount = $path_parts[0];
            $action = 'index';
            $arguments = null;
        }

        $modules = self::getMountedModules();

        if (isset($modules[$mount])) {
            return self::loadModule($modules[$mount], $action, $arguments);
        } else {
            return false;
        }
    }
}
