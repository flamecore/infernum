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
 * This class allows you to execute operations in a database
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class AbstractDriver implements DriverInterface
{
    /**
     * The database server host
     *
     * @var string
     */
    protected $host;

    /**
     * The username for authenticating at the database server
     *
     * @var string
     */
    protected $user;

    /**
     * The password for authenticating at the database server
     *
     * @var string
     */
    protected $password;

    /**
     * The name of the database
     *
     * @var string
     */
    protected $database;

    /**
     * The prefix of the database tables
     *
     * @var string
     */
    protected $prefix;

    /**
     * The link identifier of the connection
     *
     * @var mysqli
     */
    protected $link;

    /**
     * The number of executed queries
     *
     * @var int
     */
    protected $queryCount = 0;

    /**
     * Currently in transaction?
     *
     * @var bool
     */
    protected $inTransaction = false;

    /**
     * Constructor
     *
     * @param string $host The database server host, mostly 'localhost'
     * @param string $user The username for authenticating at the database server
     * @param string $password The password for authenticating at the database server
     * @param string $database The name of the database
     * @param string $prefix The prefix of the database tables
     */
    public function __construct($host = 'localhost', $user = 'root', $password = '', $database, $prefix)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->prefix = $prefix;

        $this->connect();
    }

    /**
     * Gets the number of already executed SQL operations
     *
     * @return int Returns the number of already executed SQL operations
     */
    public function getQueryCount()
    {
        return $this->queryCount;
    }

    /**
     * Prepares a PHP value for use in a SQL statement
     *
     * @param mixed $value The value to prepare
     * @return string
     */
    protected function prepareValue($value)
    {
        if (is_numeric($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return (int) $value;
        } elseif (is_string($value)) {
            return '"'.$this->quote($value).'"';
        } elseif (is_array($value)) {
            return '"'.$this->quote(implode(',', $value)).'"';
        } elseif (is_object($value)) {
            return (string) $value;
        } else {
            return 'NULL';
        }
    }

    /**
     * Prepares a SQL statement. Replaces `@HOST@`, `@USER@`, `@DATABASE@`, `@PREFIX@` and `{variables}`, if neccessary.
     *
     * @param string $query The SQL query to prepare
     * @param array $vars An array of values replacing the variables. Only neccessary if you're using variables.
     * @return string
     */
    protected function prepareQuery($query, $vars = null)
    {
        $query = str_replace('@HOST@', $this->host, $query);
        $query = str_replace('@USER@', $this->user, $query);
        $query = str_replace('@DATABASE@', $this->database, $query);
        $query = str_replace('@PREFIX@', $this->prefix, $query);

        if (is_array($vars)) {
            foreach ($vars as $key => $value) {
                $value = $this->prepareValue($value);
                $query = str_replace('{'.$key.'}', $value, $query);
            }
        }

        return $query;
    }
}
