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
 * Autoloader for classes
 *
 * @author  Christian Neff <christian.neff@gmail.com>
 */
class WebworkLoader {
    
    /**
     * Path of the currently loaded module
     * @var      string
     * @access   private
     * @static
     */
    private static $modulePath;
    
    /**
     * Returns a list of possible source paths
     * @return   array
     * @access   public
     * @static
     */
    public static function getSources() {
        $sources = array(WW_ENGINE_PATH);
        
        if (isset(self::$modulePath))
            array_push($sources, self::$modulePath);
        
        array_push($sources, WW_SITE_PATH, WW_SHARED_PATH);
        
        return $sources;
    }

    /**
     * Autoloader
     * @param    string   $name   Name of the class to load
     * @return   bool
     * @access   public
     * @static
     */
    public static function loadClass($name) {
        $name = str_replace('_', '/', $name);
        $classfile = self::find($name, 'libraries/classes/*.class.php');
        
        if ($classfile)
            require_once $classfile;

        return false;
    }
    
    /**
     * Searches for a script in all source paths and returns the filepath
     * @param    string   $name   Name of the script
     * @param    string   $path   Path pattern of the file (appended to source path)
     * @return   string
     * @access   public
     * @static
     */
    public static function find($name, $path) {
        foreach (self::getSources() as $source) {
            $file = str_replace('*', $name, $source.'/'.$path);
			
            if (file_exists($file))
                return $file;
        }
		
		return false;
    }

    /**
     * Sets the module path
     * @param    string   $path   The module path
     * @return   void
     * @access   public
     * @static
     */
    public static function setModulePath($path) {
        self::$modulePath = $path;
    }
    
}

// Register the autoloader
spl_autoload_register(array('WebworkLoader', 'loadClass'));
