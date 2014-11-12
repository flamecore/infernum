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

namespace FlameCore\Infernum\Database\Base;

/**
 * This class allows you to execute operations in a database
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class Connection
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
     * Connects to the database server and selects the database using the given configuration
     *
     * @return bool
     */
    abstract public function connect();

    /**
     * Closes the database connection
     *
     * @return void
     */
    abstract public function disconnect();

    /**
     * Performs a (optionally prepared) query on the database.
     *
     * @param string $query The SQL query to be executed
     * @param array $vars An array of values replacing the variables. Only neccessary if you're using variables.
     * @return Database_Base_Result|bool Returns a Database_Base_Result object sFor successful SELECT, SHOW, DESCRIBE or EXPLAIN queries.
     *   For other successful queries it will return TRUE.
     */
    abstract public function query($query, $vars = null);

    /**
     * Performs a SELECT query
     *
     * @param string $table The database table to query
     * @param string $columns The selected columns (Default: '*')
     * @param array $params One or more of the following parameters: (optional)
     *   * where:    The WHERE clause
     *   * vars:     An array of values replacing the variables (if neccessary)
     *   * limit:    The result row LIMIT
     *   * order:    The ORDER BY parameter
     *   * group:    The GROUP BY parameter
     * @return Database_Base_Result Returns an object of Database_Base_Result on success
     */
    abstract public function select($table, $columns = '*', $params = array());

    /**
     * Performs an INSERT query
     *
     * @param string $table The database table to fill
     * @param array $data The data to insert in the form [column => value]
     * @return bool Returns TRUE on success
     */
    abstract public function insert($table, $data);

    /**
     * Performs an UPDATE query
     *
     * @param string $table The database table to query
     * @param array $data The new data in the form [column => value]
     * @param array $params One or more of the following parameters: (optional)
     *   * where:    The WHERE clause
     *   * vars:     An array of values replacing the variables (if neccessary)
     *   * limit:    The result row LIMIT
     * @return bool Returns TRUE on success
     */
    abstract public function update($table, $data, $params = array());

    /**
     * Parses and executes a SQL dump file
     *
     * @param string $file The path to the dump file
     * @param array $vars An array of values replacing the variables. Only neccessary if you're using variables.
     * @return bool Returns TRUE on success
     */
    abstract public function importDump($file, $vars = null);

    /**
     * Gets the number of affected rows
     *
     * @return int Returns the number of rows affected by the last INSERT, UPDATE, REPLACE or DELETE query.
     *   For SELECT statements it returns the number of rows in the result set.
     */
    abstract public function affectedRows();

    /**
     * Gets the ID generated by a query on a table with a column having the AUTO_INCREMENT attribute
     *
     * @return int Returns the ID generated by a query on a table with a column having the AUTO_INCREMENT attribute.
     *   If the last query wasn't an INSERT or UPDATE statement or if the modified table does not have a column with
     *   the AUTO_INCREMENT attribute, this function will return 0.
     */
    abstract public function insertID();

    /**
     * Starts a transaction
     *
     * @return void
     */
    abstract public function beginTransaction();

    /**
     * Ends a transaction and commits remaining queries
     *
     * @return void
     */
    abstract public function endTransaction();

    /**
     * Commits the current transaction
     *
     * @return bool Returns TRUE on success or FALSE on failure or if no transaction is active
     */
    abstract public function commit();

    /**
     * Rolls back the current transaction
     *
     * @return bool Returns TRUE on success or FALSE on failure or if no transaction is active
     */
    abstract public function rollback();

    /**
     * Escapes special characters in a string for use in an SQL statement, taking into account the current charset of the connection
     *
     * @param string $string The string to be escaped
     * @return string Returns the escaped string
     */
    abstract public function quote($string);

    /**
     * Gets the last error message for the most recent query that can succeed or fail
     *
     * @return string Returns the last error message
     */
    abstract public function getError();

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
