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
 * Helper class for working with databases
 *
 * @author  Christian Neff <christian.neff@gmail.com>
 */
class Database {
    
    /**
     * Creates a new database driver instance. Omitted parameters will fall back to the values from the configuration.
     * @param    string   $driver     The database driver to use
     * @param    string   $host       The database server host, mostly 'localhost'
     * @param    string   $user       The username for authenticating at the database server
     * @param    string   $password   The password for authenticating at the database server
     * @param    string   $database   The name of the database
     * @param    string   $prefix     The prefix of the database tables
     * @return   object
     * @access   public
     * @static
     */
    public static function loadDriver($driver, $host, $user, $password, $database, $prefix) {
        if ($driver == '' || !in_array($driver, self::getAvailableDrivers()))
            throw new Exception('Database driver "'.$driver.'" not found or invalid');
        
        $driverClass = 'Database_'.$driver.'_Driver';
        return new $driverClass($host, $user, $password, $database, $prefix);
    }
    
    /**
     * Returns a list of available drivers.
     * @return   array
     * @access   public
     * @static
     */
    public static function getAvailableDrivers() {
        $drivers = glob(WW_ENGINE_PATH.'/includes/classes/Database/*', GLOB_ONLYDIR);
        return array_map('basename', $drivers);
    }
    
}
