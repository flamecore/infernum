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
 * Result set returned by a MySQL query
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Database_MySQL_Result extends Database_Base_Result {

    /**
     * Gets a result row as an enumerated array. Returns an array of strings that corresponds to the fetched row
     *   or NULL if there are no more rows in result set.
     * @return   array
     * @access   public
     */
    public function fetchRow() {
        return mysqli_fetch_row($this->_result);
    }

    /**
     * Gets a single cell from the first row of the result set. Returns the value of the cell or NULL on failure.
     * @param    int      $index   The index of the cell to fetch. Defaults to 0.
     * @return   mixed
     * @access   public
     */
    public function fetchCell($index = 0) {
        $row = mysqli_fetch_row($this->_result);
        
        if (isset($row[$index])) {
            return $row[$index];
        } else {
            return null;
        }
    }

    /**
     * Fetches a result row as an associative array, a numeric array, or both. Returns an array of strings that
     *   corresponds to the fetched row or NULL if there are no more rows in the resultset.
     * @param    string   $type   This optional parameter indicates what type of array should be produced from the current
     *                              row data. The possible values for this parameter are 'num', 'assoc' or 'both'.
     *                              Default: 'both'.
     * @return   array
     * @access   public
     */
    public function fetchArray($type = 'both') {
        switch ($type) {
            case 'num': $type = MYSQLI_NUM; break;
            case 'assoc': $type = MYSQLI_ASSOC; break;
            default: case 'both': $type = MYSQLI_BOTH; break;
        }
        
        return mysqli_fetch_array($this->_result, $type);
    }

    /**
     * Fetches a result row as an associative array. Returns an associative array of strings representing the fetched row
     *   in the result set, where each key in the array represents the name of one of the result set's columns or NULL if
     *   there are no more rows in resultset.
     * @return   array
     * @access   public
     */
    public function fetchAssoc() {
        return mysqli_fetch_assoc($this->_result);
    }
    
    /**
     * Gets the values of a single column for each row of the result set. Returns the values of each cell as an array.
     * @param    int      $index   The index of the cell to fetch. Defaults to 0.
     * @return   array
     * @access   public
     */
    public function fetchColumn($index = 0) {
        $return = array();
        while ($row = mysqli_fetch_row($this->_result))
            $return[] = $row[$index];
        return $return;
    }

    /**
     * Fetches all result rows as an associative array, a numeric array or both. Returns an array of associative or numeric
     *   arrays holding result rows.
     * @param    string   $type   This optional parameter indicates what type of array should be produced from the current
     *                              row data. The possible values for this parameter are 'num', 'assoc' or 'both'.
     *                              Default: 'assoc'.
     * @return   array
     * @access   public
     */
    public function fetchAll($type = 'assoc') {
        switch ($type) {
            case 'num': $type = MYSQLI_NUM; break;
            case 'assoc': $type = MYSQLI_ASSOC; break;
            default: case 'both': $type = MYSQLI_BOTH; break;
        }
        
        if (function_exists('mysqli_fetch_all')) {
            return mysqli_fetch_all($this->_result, $type);
        } else {
            $return = array();
            while ($row = mysqli_fetch_array($this->_result, $type))
                $return[] = $row;
            return $return;
        }
    }

    /**
     * Gets the number of rows in a result
     * @return   int
     * @access   public
     */
    public function numRows() {
        return mysqli_num_rows($this->_result);
    }

    /**
     * Gets the number of fields in a result
     * @return   int
     * @access   public
     */
    public function numFields() {
        return mysqli_num_fields($this->_result);
    }

    /**
     * Frees the memory associated with the result
     * @return   void
     * @access   public
     */
    public function free() {
        return mysqli_free_result($this->_result);
    }

}
