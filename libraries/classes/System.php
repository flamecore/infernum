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
     * @access   public
     * @static
     */
    public static $settings = array();
    
    /**
     * The database driver object
     * @var      Database_Base_Driver
     * @access   public
     * @static
     */
    public static $db;
    
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
        // At first we have to load the settings
        self::$settings = get_cached('settings', function() {
            return parse_settings(WW_SITE_PATH.'/settings.ini');
        });
        
        // Make sure that the required settings are available and shut down the system otherwise
        if (!isset(self::$settings['main']) || !isset(self::$settings['database']))
            trigger_error('Required settings "main" and/or "database" not available', E_USER_ERROR);
        
        // Now we can load our database driver
        $driver = self::$settings['database']['driver'];
        $host = self::$settings['database']['host'];
        $user = self::$settings['database']['user'];
        $password = self::$settings['database']['password'];
        $database = self::$settings['database']['database'];
        $prefix = self::$settings['database']['prefix'];
        
        self::$db = Database::loadDriver($driver, $host, $user, $password, $database, $prefix);
        
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
     * Loads a module controller
     * @param    string   $module      The name of the module
     * @param    string   $arguments   The arguments to use
     * @return   void
     * @access   public
     * @static
     */
    public static function loadModule($module, $arguments) {
        $argsList = explode('/', $arguments);

        $modulePath = WW_SITE_PATH.'/modules/'.$module;
        $moduleFile = $modulePath.'/controller.php';

        if (!file_exists($moduleFile))
            error(404);

        WebworkLoader::setModulePath($modulePath);
        
        include $moduleFile;
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

        self::loadModule($module, $arguments);
    }
    
}
