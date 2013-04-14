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
 * This class allows you to execute operations in a MySQL database
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Database_MySQL_Driver extends Database_Base_Driver {

    /**
     * Connects to the database server and selects the database using the given configuration
     * @return   bool
     * @access   public
     */
    public function connect() {
        $this->_link = @mysqli_connect($this->_host, $this->_user, $this->_password, $this->_database);
        
        if (mysqli_connect_errno())
            throw new Exception('Failed connecting to the database: '.mysqli_connect_error());
    }

    /**
     * Closes the database connection
     * @return   void
     * @access   public
     */
    public function disconnect() {
        mysqli_close($this->_link);
    }

    /**
     * Performs a (optionally prepared) query on the database. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries
     *   it returns a Database_MySQL_Result object on success. For other successful queries it will return TRUE.
     * @param    string   $query   The SQL query to be executed
     * @param    array    $vars    An array of values replacing the variables. Only neccessary if you're using variables.
     * @return   mixed
     * @access   public
     */
    public function query($query, $vars = null) {
        $query = $this->_prepareQuery($query, $vars);
        
        $result = @mysqli_query($this->_link, $query);
        if ($result) {
            $this->_queryCount++;
            
            if ($result instanceof MySQLi_Result)
                return new Database_MySQL_Result($result);
            
            return true;
        }
        
        throw new Exception('Database query failed: '.$this->getError());
    }
    
    /**
     * Performs a SELECT query. Returns a Database_Base_Result object on success.
     * @param    string   $table     The database table to query
     * @param    string   $columns   The selected columns. Defaults to '*'.
     * @param    array    $params    One or more of the following parameters: (optional)
     *                                 * where    The WHERE clause
     *                                 * vars     An array of values replacing the variables (if neccessary)
     *                                 * limit    The result row LIMIT
     *                                 * order    The ORDER BY parameter
     *                                 * group    The GROUP BY parameter
     * @return   Database_Base_Result
     * @access   public
     */
    public function select($table, $columns = '*', $params = array()) {
        $sql = 'SELECT '.$columns.' FROM `'.$table.'`';

        if (isset($params['where']))
            $sql .= ' WHERE '.$params['where'];
        if (isset($params['limit']))
            $sql .= ' LIMIT '.$params['limit'];
        if (isset($params['group']))
            $sql .= ' GROUP BY '.$params['group'];
        if (isset($params['order']))
            $sql .= ' ORDER BY '.$params['order'];

        return $this->query($sql, isset($params['vars']) ? $params['vars'] : null);
    }
    
    /**
     * Performs an INSERT query. Returns TRUE on success.
     * @param    string   $table   The database table to fill
     * @param    array    $data    The data to insert in the form [column => value]
     * @return   bool
     * @access   public
     */
    public function insert($table, $data) {
        foreach ($data as $column => $value) {
            $columns[] = '`'.$column.'`';
            $values[]  = $this->_prepareValue($value);
        }

        $sql = 'INSERT INTO `'.$table.'` ('.implode(', ', $columns).') VALUES('.implode(', ', $values).')';
        
        return $this->query($sql);
    }
    
    /**
     * Performs an UPDATE query. Returns TRUE on success.
     * @param    string   $table    The database table to query
     * @param    array    $data     The new data in the form [column => value]
     * @param    array    $params   One or more of the following parameters: (optional)
     *                                * where    The WHERE clause
     *                                * vars     An array of values replacing the variables (if neccessary)
     *                                * limit    The result row LIMIT
     * @return   bool
     * @access   public
     */
    public function update($table, $data, $params = array()) {
        foreach ($data as $key => $value) {
            $value = $this->_prepareValue($value);
            $dataset[] = '`'.$key.'` = '.$value;
        }

        $sql = 'UPDATE `'.$table.'` SET '.implode(', ', $dataset);

        if (isset($params['where']))
            $sql .= ' WHERE '.$params['where'];
        if (isset($params['limit']))
            $sql .= ' LIMIT '.$params['limit'];

        return $this->query($sql, isset($params['vars']) ? $params['vars'] : null);
    }

    /**
     * Parses and executes a SQL dump file
     * @param    string   $file   The path to the dump file
     * @param    array    $vars   An array of values replacing the variables. Only neccessary if you're using variables.
     * @return   bool
     * @access   public
     */
    public function importDump($file, $vars = null) {
        $dumpContent = file_get_contents($file);
        $queries = preg_split('/;\s*$/', $dumpContent);
        
        $this->beginTransaction();
        
        foreach ($queries as $query) {
            $query = trim($query);
            if ($query == '' || substr($query, 0, 2) == '--')
                continue;
            $this->query($query, $vars);
        }
        
        $this->endTransaction();
    }

    /**
     * Returns the number of rows affected by the last INSERT, UPDATE, REPLACE or DELETE query. For SELECT statements
     *   it returns the number of rows in the result set.
     * @return   int
     * @access   public
     */
    public function affectedRows() {
        return mysqli_affected_rows($this->_link);
    }

    /**
     * Returns the ID generated by a query on a table with a column having the AUTO_INCREMENT attribute. If the last
     *   query wasn't an INSERT or UPDATE statement or if the modified table does not have a column with the AUTO_INCREMENT
     *   attribute, this function will return 0.
     * @return   int
     * @access   public
     */
    public function insertID() {
        return mysqli_insert_id($this->_link);
    }

    /**
     * Starts a transaction
     * @return   void
     * @access   public
     */
    public function startTransaction() {
        mysqli_autocommit($this->_link, false);
        $this->_inTransaction = true;
    }

    /**
     * Ends a transaction and commits remaining queries
     * @return   void
     * @access   public
     */
    public function endTransaction() {
        mysqli_autocommit($this->_link, true);
        $this->_inTransaction = false;
    }

    /**
     * Commits the current transaction. Returns TRUE on success or FALSE on failure or if no transaction is active.
     * @return   bool
     * @access   public
     */
    public function commit() {
        if ($this->_inTransaction) {
            return mysqli_commit($this->_link);
        } else {
            return false;
        }
    }

    /**
     * Rolls back the current transaction. Returns TRUE on success or FALSE on failure or if no transaction is active.
     * @return   bool
     * @access   public
     */
    public function rollback() {
        if ($this->_inTransaction) {
            return mysqli_rollback($this->_link);
        } else {
            return false;
        }
    }

    /**
     * Escapes special characters in a string for use in an SQL statement, taking into account the current charset of the connection
     * @param    string   $string   The string to be escaped
     * @return   string
     * @access   public
     */
    public function quote($string) {
        return mysqli_real_escape_string($this->_link, $string);
    }

    /**
     * Returns the last error message for the most recent query that can succeed or fail
     * @return  string
     * @access  public
     */
    public function getError() {
        return mysqli_error($this->_link);
    }

}
