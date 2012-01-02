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
 * @author  Christian Neff <christian.neff@gmail.com>
 */
class Settings {

    /**
     * All loaded settings
     * @var     array
     * @access  private
     * @static
     */
    private static $_settings = array();

    /**
     * Loads all settings
     * @return  void
     * @access  public
     * @static
     */
    public static function init() {
        $cache = new Cache('settings');
        if ($cache->active) {
            // load settings from cache
            self::$_settings = $cache->read();
        } else {
            // load settings from config files in settings dir
            self::$_settings = self::_loadFromDir(WW_DIR_SETTINGS);
            
            // write to cache if enabled
            $cache->store(self::$_settings);
        }
    }

    /**
     * Gets all settings of a section (as array) or the value of a specific setting
     * @param   string  $section  From this section...
     * @param   string  $setting  ... grab this setting, optional
     * @return  mixed
     * @access  public
     * @static
     */
    public static function get($section, $setting = null) {
        if (is_string($setting)) {
            return self::$_settings[$section][$setting];
        } else {
            return self::$_settings[$section];
        }
    }

    /**
     * Writes the given settings to a specified settings file
     * @param   string  $section   The name of the settings section (the file, name without '.php')
     * @param   array   $settings  The settings to write
     * @return  bool
     * @access  public
     * @static
     */
    public static function write($section, $settings) {
        // make settings list
        $list = array();
        foreach ($settings as $settingKey => $settingVal)
            $list[] = "    '{$settingKey}' => {$settingVal}";
        
        // generate content
        $file = HADES_DIR_SETTINGS.'/'.$section.'.php';
        $content  = "\$settings['{$section}'] = array(\n";
        $content .= implode(",\n", $list);
        $content .= "\n);";
        
        return file_put_contents($file, $content);
    }

    /**
     * Reads all settings from the given directory, works recursively through all subdirectories
     * @param   string  $dir  The absolute path to the directory to scan
     * @return  array
     * @access  private
     * @static
     * @author  Sebastian Wagner <szebi@gmx.at>
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
