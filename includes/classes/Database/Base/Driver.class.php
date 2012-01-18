<?php
/**
 * Webwork
 * Copyright (C) 2011 IceFlame.net
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
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
 * This class allows you to execute operations in a database
 *
 * @author  Christian Neff <christian.neff@gmail.com>
 */
abstract class Database_Base_Driver {

    /**
     * The database server host
     * @var      string
     * @access   protected
     */
    protected $_host;

    /**
     * The username for authenticating at the database server
     * @var      string
     * @access   protected
     */
    protected $_user;

    /**
     * The password for authenticating at the database server
     * @var      string
     * @access   protected
     */
    protected $_password;

    /**
     * The name of the database
     * @var      string
     * @access   protected
     */
    protected $_database;

    /**
     * The prefix of the database tables
     * @var      string
     * @access   protected
     */
    protected $_prefix;

    /**
     * The link identifier of the connection
     * @var      mysqli
     * @access   protected
     */
    protected $_link;

    /**
     * The number of executed queries
     * @var      int
     * @access   protected
     */
    protected $_queryCount = 0;

    /**
     * Currently in transaction?
     * @var      bool
     * @access   protected
     */
    protected $_inTransaction = false;

    /**
     * Constructor
     * @param    string   $host       The database server host, mostly 'localhost'
     * @param    string   $user       The username for authenticating at the database server
     * @param    string   $password   The password for authenticating at the database server
     * @param    string   $database   The name of the database
     * @param    string   $prefix     The prefix of the database tables
     * @return   void
     * @access   public
     */
    public function __construct($host = 'localhost', $user = 'root', $password = '', $database, $prefix) {
        $this->_host = $host;
        $this->_user = $user;
        $this->_password = $password;
        $this->_database = $database;
        $this->_prefix = $prefix;
        
        $this->connect();
    }

    /**
     * Connects to the database server and selects the database using the given configuration
     * @return   bool
     * @access   public
     * @abstract
     */
    abstract public function connect();

    /**
     * Closes the database connection
     * @return   void
     * @access   public
     * @abstract
     */
    abstract public function disconnect();

    /**
     * Performs a (optionally prepared) query on the database. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries
     *   it returns a Database_Base_Result object on success. For other successful queries it will return TRUE.
     * @param    string   $query   The SQL query to be executed
     * @param    array    $vars    An array of values replacing the variables. Only neccessary if you're using variables.
     * @return   mixed
     * @access   public
     * @abstract
     */
    abstract function query($query, $vars = null);

    /**
     * Parses and executes a SQL dump file
     * @param    string   $file   The path to the dump file
     * @param    array    $vars   An array of values replacing the variables. Only neccessary if you're using variables.
     * @return   bool
     * @access   public
     * @abstract
     */
    abstract public function importDump($file, $vars = null);

    /**
     * Returns the number of rows affected by the last INSERT, UPDATE, REPLACE or DELETE query. For SELECT statements
     *   it returns the number of rows in the result set.
     * @return   int
     * @access   public
     * @abstract
     */
    abstract public function affectedRows();

    /**
     * Returns the ID generated by a query on a table with a column having the AUTO_INCREMENT attribute. If the last
     *   query wasn't an INSERT or UPDATE statement or if the modified table does not have a column with the AUTO_INCREMENT
     *   attribute, this function will return 0.
     * @return   int
     * @access   public
     * @abstract
     */
    abstract public function insertID();

    /**
     * Starts a transaction
     * @return   void
     * @access   public
     * @abstract
     */
    abstract public function startTransaction();

    /**
     * Ends a transaction and commits remaining queries
     * @return   void
     * @access   public
     * @abstract
     */
    abstract public function endTransaction();

    /**
     * Commits the current transaction. Returns TRUE on success or FALSE on failure or if no transaction is active.
     * @return   bool
     * @access   public
     * @abstract
     */
    abstract public function commit();

    /**
     * Rolls back the current transaction. Returns TRUE on success or FALSE on failure or if no transaction is active.
     * @return   bool
     * @access   public
     * @abstract
     */
    abstract public function rollback();

    /**
     * Escapes special characters in a string for use in an SQL statement, taking into account the current charset of the connection
     * @param    string   $string   The string to be escaped
     * @return   string
     * @access   public
     * @abstract
     */
    abstract public function quote($string);

    /**
     * Returns the last error message for the most recent query that can succeed or fail
     * @return   string
     * @access   public
     * @abstract
     */
    abstract public function getError();

    /**
     * Returns the number of already executed SQL operations
     * @return   int
     * @access   public
     */
    public function getQueryCount() {
        return $this->_queryCount;
    }

    /**
     * Prepares a PHP value for use in a SQL statement
     * @param    mixed    $value   The value to prepare
     * @return   string
     * @access   protected
     */
    protected function _prepareValue($value) {
        if (is_numeric($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return (int) $value;
        } elseif (is_string($value)) {
            return '"'.$this->quote($value).'"';
        } elseif (is_array($value)) {
            return '"'.$this->quote(implode(',', $value)).'"';
        } else {
            return 'NULL';
        }
    }

    /**
     * Prepares a SQL statement. Replaces @HOST@, @USER@, @DATABASE@, @PREFIX@ and {variables}, if neccessary.
     * @param     string   $query   The SQL query to prepare
     * @param     array    $vars    An array of values replacing the variables. Only neccessary if you're using variables.
     * @return    string
     * @accesss   protected
     */
    protected function _prepareQuery($query, $vars = null) {
        $query = str_replace('@HOST@', $this->_host, $query);
        $query = str_replace('@USER@', $this->_user, $query);
        $query = str_replace('@DATABASE@', $this->_name, $query);
        $query = str_replace('@PREFIX@', $this->_prefix, $query);
        
        if (is_array($vars)) {
            foreach ($vars as $key => $value) {
                $value = $this->_prepareValue($value);
                $query = str_replace('{'.$key.'}', $value, $query);
            }
        }
        
        return $query;
    }

}
