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
 * Class for managing the basic core features
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 * @author   Sebastian Wagner <szebi@gmx.at>
 */
class System {

    /**
     * All loaded settings
     * @var      array
     * @access   private
     * @static
     */
    private static $_settings = array();
    
    /**
     * The database driver object
     * @var      Database_Connection
     * @access   private
     * @static
     */
    private static $_db;
    
    /**
     * Is the system initialized?
     * @var      bool
     * @access   private
     * @static
     */
    private static $_initialized = false;

    /**
     * Initializes the system
     * @return   void
     * @access   public
     * @static
     */
    public static function startup() {
        if (!is_dir(WW_SITE_PATH))
            trigger_error('Directory of site "'.WW_SITE_NAME.'" does not exist', E_USER_ERROR);
        
        // At first we have to load the settings
        $cache = new Cache('settings');
        self::$_settings = $cache->data(function () {
            return parse_settings(WW_SITE_PATH.'/settings.yml');
        });
        
        // Make sure that the required settings are available and shut down the system otherwise
        if (!isset(self::$_settings['Main']) || !isset(self::$_settings['Database']))
            trigger_error('Required settings "Main" and/or "Database" not available', E_USER_ERROR);
        
        // Now we can load our database driver
        $driver = self::$_settings['Database']['Driver'];
        $host = self::$_settings['Database']['Host'];
        $user = self::$_settings['Database']['User'];
        $password = self::$_settings['Database']['Password'];
        $database = self::$_settings['Database']['Database'];
        $prefix = self::$_settings['Database']['Prefix'];
        
        self::$_db = new Database_Connection($driver, $host, $user, $password, $database, $prefix);
        
        // All systems are started now and running smoothly
        self::$_initialized = true;
    }
	
    /**
     * Checks if the sytem has been started
     * @return   bool
     * @access   public
     * @static
     */
    public static function isStarted() {
        return self::$_initialized;
    }

    /**
     * Returns the value of a setting
     * @param    string   $setting   The settings key in the form "<section>:<keyname>"
     * @param    mixed    $default   Custom default value (optional)
     * @return   mixed
     * @access   public
     * @static
     */
    public static function setting($section, $keyname = null, $default = false) {
        if (!self::isStarted())
            trigger_error('The system is not yet ready', E_USER_ERROR);
        
        if (isset($keyname)) {
            return isset(self::$_settings[$section][$keyname]) ? self::$_settings[$section][$keyname] : $default;
        } else {
            return isset(self::$_settings[$section]) ? self::$_settings[$section] : $default;
        }
    }
    
    /**
     * Returns the database driver object
     * @return   Database_Base_Driver
     * @access   public
     * @static
     */
    public static function db() {
        if (!self::isStarted())
            trigger_error('The system is not yet ready', E_USER_ERROR);
        
        return self::$_db;
    }

    /**
     * Loads a module controller
     * @param    string   $module      The name of the module
     * @param    string   $arguments   The arguments to use
     * @return   void
     * @access   public
     * @static
     */
    public static function loadModule($module, $arguments) {
        if (!self::isStarted())
            trigger_error('The system is not yet ready', E_USER_ERROR);

        if (defined('WW_MODULE'))
            trigger_error('A module has already been loaded', E_USER_ERROR);

        define('WW_MODULE', $module);
        define('WW_MODULE_PATH', WW_SITE_PATH.'/modules/'.$module);

        $controller_file = WW_MODULE_PATH.'/controller.php';

        if (!file_exists($controller_file))
            return false;
        
        $args_list = explode('/', $arguments);

        $return = include_once $controller_file;

        return (bool) $return;
    }

    /**
     * Loads a module controller by given path
     * @param    string   $path   The path of the module page
     * @return   void
     * @access   public
     * @static
     */
    public static function loadModuleFromPath($path) {
        @list($module, $arguments) = explode('/', $path, 2);

        $module = str_replace('-', '_', $module);
        $module = strtolower($module);

        return self::loadModule($module, $arguments);
    }
    
}
