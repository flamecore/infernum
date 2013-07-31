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
     * Searches all source paths for a script with given name. By default the function returns the filepath (string) of
     *   the script that is found at first. If the parameter $findall is set to TRUE the function returns an array of
     *   all found scripts (not just the first one).
     * @param    string   $name      Name of the script
     * @param    string   $path      Path pattern of the file (appended to source path)
     * @param    string   $findall   Search for all scripts inclusively. Defaults to FALSE.
     * @return   mixed
     * @access   public
     * @static
     */
    public static function find($name, $path, $findall = false) {
        if ($findall)
            $results = array();
        
        foreach (self::getSources() as $source) {
            $file = str_replace('*', $name, $source.'/'.$path);
			
            if (file_exists($file)) {
                if (!$findall) {
                    return $file;
                } else {
                    $results[] = $file;
                }
            }
        }
        
        if ($findall && !empty($results))
            return $results;
		
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
