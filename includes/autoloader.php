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
class Autoloader {
    
    /**
     * The list of registered class directories
     * @var      array
     * @access   private
     * @static
     */
    private static $_classDirs = array();

    /**
     * The class loader
     * @param    string   $className   The name of the class to load
     * @return   bool
     * @static
     */
    public static function load($className) {
        $classFileName = str_replace('_', '/', $className);

        foreach (self::$_classDirs as $classDir) {
            $classFile = $classDir.'/'.$classFileName.'.class.php';
            if (file_exists($classFile)) {
                require_once $classFile;
                return true;
            }
        }

        return false;
    }
    
    /**
     * Adds a class directory to the list
     * @param    string   $path   The path of the class directory to add
     * @return   void
     * @static
     */
    public static function addClassPath($path) {
        self::$_classDirs[] = $path;
    }
    
}

// Register the autoloader
spl_autoload_register(array('Autoloader', 'load'));

// Add basic class paths
Autoloader::addClassPath(WW_ENGINE_PATH.'/libraries/classes');
Autoloader::addClassPath(WW_SITE_PATH.'/libraries/classes');
Autoloader::addClassPath(WW_SHARED_PATH.'/libraries/classes');
