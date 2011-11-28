<?php
/**
 * HadesLite
 * Copyright (C) 2011 Hades Project
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
 * @package     HadesLite
 * @version     0.1-dev
 * @link        http://hades.iceflame.net
 * @license     ISC License (http://www.opensource.org/licenses/ISC)
 */

/**
 * The core class provides essential functionality
 *
 * @author  Christian Neff <christian.neff@gmail.com>
 */
class Core {

    /**
     * The database connection object (Database_Base_Driver)
     * @var     object
     * @access  public
     * @static
     */
    public static $db;

    /**
     * Initializes the core and opens a new database connection
     * @return  void
     * @access  public
     * @static
     */
    public static function init() {
        $dbConfig = Settings::get('database');
        if (!self::$db instanceof Database_Base_Driver) {
            $dbDriverClass = 'Database_'.$dbConfig['driver'].'_Driver';
            self::$db = new $dbDriverClass($dbConfig['host'], $dbConfig['user'], $dbConfig['password'],
                                           $dbConfig['database'], $dbConfig['prefix']);
        }
    }

}
