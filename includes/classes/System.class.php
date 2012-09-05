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
        $settings = Cache::read('settings');
        if (!isset($settings)) {
            $settings = self::loadSettings(WW_SITE_PATH.'/settings');
            Cache::store('settings', $settings);
        }
        
        self::$settings = $settings;
        
        // Make sure that the required settings are available and shut down the system otherwise
        if (!isset(self::$settings['core']) || !isset(self::$settings['database']))
            throw new Exception('Required settings "core" and/or "database" not found!');
        
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
     * Reads all settings from the given directory, works recursively through all subdirectories
     * @param    string   $dir   The absolute path to the directory to scan
     * @return   array
     * @access   public
     * @static
     */
    public static function loadSettings($dir) {
        $settings = array();
        
        if (!is_string($dir) || !is_dir($dir))
            return;
        
        // include all configuration files, we need no sorting
        foreach (glob($dir.'/*.php', GLOB_NOSORT) as $file)
            require_once($file);
        
        // load configuration files from all subdirectories
        foreach (glob($dir.'/*', GLOB_ONLYDIR) as $subdir)
            array_push($settings, self::loadSettings($subdir));
        
        return $settings;
    }

    /**
     * Writes the given settings to a specified settings file
     * @param    string   $section    The name of the settings section (the file, name without '.php')
     * @param    array    $settings   The settings to write. If omitted, the current settings will be written.
     * @return   bool
     * @access   public
     * @static
     */
    public static function writeSettings($section, $settings = null) {
        if (!isset($settings))
            $settings = self::$settings[$section];
        
        $content = '$settings[\''.$section.'\'] = '.var_export($settings).';';
        return file_put_contents(WW_SITE_PATH.'/settings/'.$section.'.php', $content);
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
        $moduleFile = WW_SITE_PATH.'/modules/'.$module.'/controller.php';

        if (!file_exists($moduleFile)) {
            showNotFoundError();
        }

        include $moduleFile;
    }

}
