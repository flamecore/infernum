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
 * Autoloader for classes
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Autoloader
{
    /**
     * Registers the autoloader
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'loadClass']);
    }

    /**
     * Gets a list of possible source paths
     *
     * @return array Returns an array of source paths
     */
    public static function getSources()
    {
        $sources = array(WW_ENGINE_PATH);

        if (defined('WW_MODULE_PATH'))
            array_push($sources, WW_MODULE_PATH);

        array_push($sources, WW_SITE_PATH, WW_SHARED_PATH);

        return $sources;
    }

    /**
     * Loads the given class
     *
     * @param string $name Name of the class to load
     * @return bool Returns FALSE if the class could not be loaded, TRUE otherwise
     */
    public static function loadClass($name)
    {
        $name = str_replace('_', '/', $name);
        $classfile = self::find($name, 'libraries/*.php');

        if ($classfile) {
            require_once $classfile;
            return true;
        }

        return false;
    }

    /**
     * Searches all source paths for a script with given name. By default the filepath of the script that is found at first is returned.
     *   If $findall is set to TRUE an array of all found scripts (not just the first one) is returned.
     *
     * @param string $name Name of the script
     * @param string $path Path pattern of the file (appended to source path)
     * @param string $findall Search for all scripts inclusively (Default: FALSE)
     * @return mixed Returns the filepath of the script that is found at first (string) by default. If $findall is set to TRUE an array
     *   of all found scripts is returned. If no script is found FALSE is returned.
     */
    public static function find($name, $path, $findall = false)
    {
        if ($findall)
            $results = array();

        foreach (self::getSources() as $source) {
            $file = str_replace('*', str_replace('\\', '/', $class), $source.'/'.$path);

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
}

Autoloader::register();
