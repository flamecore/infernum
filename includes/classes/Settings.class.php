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
 * Class for reading and editing the configuration
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 * @author   Sebastian Wagner <szebi@gmx.at>
 */
class Settings {

    /**
     * All loaded settings
     * @var      array
     * @access   private
     * @static
     */
    private static $_settings = array();

    /**
     * Loads all settings
     * @return   void
     * @access   public
     * @static
     */
    public static function init() {
        $cache = new Cache('settings');
        if ($cache->active) {
            // load settings from cache
            self::$_settings = $cache->read();
        } else {
            // load settings from config files in settings dir
            self::$_settings = self::_loadFromDir(WW_SITE_PATH.'/settings');
            
            // write to cache if enabled
            $cache->store(self::$_settings);
        }
    }

    /**
     * Returns all settings, the settings of a section (as array) or the value of a specific setting
     * @param    string   $section   Get setting(s) from this section. Optional.
     * @param    string   $setting   Get this setting from the given section. Optional.
     * @return   mixed
     * @access   public
     * @static
     */
    public static function get($section = null, $setting = null) {
        if (isset($section)) {
            if (!isset(self::$_settings[$section]))
                throw new Exception('Settings section "'.$section.'" does not exist.');
            
            if (isset($setting)) {
                if (!isset(self::$_settings[$section][$setting]))
                    return false;
                
                return self::$_settings[$section][$setting];
            } else {
                return self::$_settings[$section];
            }
        } else {
            return self::$_settings;
        }
    }
    
    /**
     * Temporarily alters the given setting
     * @param    string   $section   Alters setting in this section
     * @param    string   $setting   Alters this setting in the given section
     * @param    mixed    $value     The new value of the setting
     * @return   mixed
     * @access   public
     * @static
     */
    public static function alter($section, $setting, $value) {
        self::$_settings[$section][$setting] = $value;
    }

    /**
     * Writes the given settings to a specified settings file
     * @param    string   $section    The name of the settings section (the file, name without '.php')
     * @param    array    $settings   The settings to write. If omitted, the current settings will be written.
     * @return   bool
     * @access   public
     * @static
     */
    public static function write($section, $settings = null) {
        if (!isset ($settings))
            $settings = self::$_settings[$section];
        
        $content = '$settings[\''.$section.'\'] = '.var_export($settings).';';
        return file_put_contents(WW_ENGINE_PATH.'/settings/'.$section.'.php', $content);
    }

    /**
     * Reads all settings from the given directory, works recursively through all subdirectories
     * @param    string   $dir   The absolute path to the directory to scan
     * @return   array
     * @access   private
     * @static
     */
    private static function _loadFromDir($dir) {
        $settings = array();
        
        if (!is_string($dir) || !is_dir($dir))
            return;
        
        // include all configuration files, we need no sorting
        foreach (glob($dir.'/*.php', GLOB_NOSORT) as $file)
            require_once($file);
        
        // load configuration files from all subdirectories
        foreach (glob($dir.'/*', GLOB_ONLYDIR) as $subdir)
            array_push($settings, self::_loadFromDir($subdir));
        
        return $settings;
    }

}
