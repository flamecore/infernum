<?php
/**
 * Infernum
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
 * @package  FlameCore\Infernum
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

namespace FlameCore\Infernum\Database;

/**
 * Class for managing database connections
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Database
{
    private static $drivers = array(
        'mysql' => 'MySQL'
    );

    /**
     * Opens a new database connection.
     *
     * @param string $driver The database driver to use
     * @param string $host The database server host (Default: 'localhost')
     * @param string $user The username for authenticating at the database server (Default: 'root')
     * @param string $password The password for authenticating at the database server (Default: empty)
     * @param string $database The name of the database
     * @param array $options An array of options (prefix, charset)
     * @return \FlameCore\Infernum\Database\DriverInterface Returns the Driver object.
     */
    public static function connect($driver, $host = 'localhost', $user = 'root', $password = '', $database, array $options = [])
    {
        $driverClass = self::getDriverClass($driver);

        if (!class_exists($driverClass)) {
            throw new \DomainException(sprintf('Database driver class "%s" is not available.', $driverClass));
        }

        if (!is_string($database) || empty($database)) {
            throw new \InvalidArgumentException('Database name is invalid.');
        }

        $driver = new $driverClass($host, $user, $password, $database);

        if (isset($options['prefix'])) {
            $driver->setPrefix($options['prefix']);
        }

        if (isset($options['charset'])) {
            $driver->setCharset($options['charset']);
        }

        return $driver;
    }

    /**
     * Opens a new database connection using the given DSN.
     *
     * @param string $dsn The Data Source Name (driver://user:password@host/database[?option=value&...])
     * @return \FlameCore\Infernum\Database\DriverInterface Returns the Driver object.
     */
    public static function connectDsn($dsn)
    {
        $params = parse_url($dsn);

        $driver = isset($params['scheme']) ? $params['scheme'] : false;
        $host = isset($params['host']) ? $params['host'] : null;
        $user = isset($params['user']) ? $params['user'] : null;
        $password = isset($params['pass']) ? $params['pass'] : null;
        $database = isset($params['path']) ? trim($params['path'], '/ ') : false;

        parse_str($params['query'], $options);

        return self::connect($driver, $host, $user, $password, $database, $options);
    }

    /**
     * Gets the name of the driver class.
     *
     * @param string $driver The name of the driver
     * @return string Returns the name of the driver class.
     */
    private static function getDriverClass($driver)
    {
        $driver = (string) $driver;

        if ($driver === '')
            throw new \InvalidArgumentException('Database driver name is invalid.');

        $driver = strtolower($driver);

        if (!isset(self::$drivers[$driver]))
            throw new \DomainException(sprintf('Database driver "%s" is not supported.', $driver));

        return sprintf('%1$s\%2$s\%2$sDriver', __NAMESPACE__, self::$drivers[$driver]);
    }
}
