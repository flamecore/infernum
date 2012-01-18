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
 * Result set returned by a database query
 *
 * @author  Christian Neff <christian.neff@gmail.com>
 */
abstract class Database_Base_Result {
    
    /**
     * The result object returned by the corresponding query
     * @var      object
     * @access   protected
     */
    protected $_result;

    /**
     * Constructor
     * @param    object   $result   The result object returned by the corresponding query
     * @return   void
     * @access   public
     */
    public function __construct($result) {
        $this->_result =& $result;
    }

    /**
     * Gets a result row as an enumerated array. Returns an array of strings that corresponds to the fetched row
     *   or NULL if there are no more rows in result set.
     * @return   array
     * @access   public
     * @abstract
     */
    abstract public function fetchRow();

    /**
     * Fetches a result row as an associative array, a numeric array, or both. Returns an array of strings that
     *   corresponds to the fetched row or NULL if there are no more rows in the resultset.
     * @param    string   $type   This optional parameter indicates what type of array should be produced from the current
     *                              row data. The possible values for this parameter are 'num', 'assoc' or 'both'.
     *                              Default: 'both'.
     * @return   array
     * @access   public
     * @abstract
     */
    abstract public function fetchArray($type = 'both');

    /**
     * Fetches a result row as an associative array. Returns an associative array of strings representing the fetched row
     *   in the result set, where each key in the array represents the name of one of the result set's columns or NULL if
     *   there are no more rows in resultset.
     * @return   array
     * @access   public
     * @abstract
     */
    abstract public function fetchAssoc();

    /**
     * Fetches all result rows as an associative array, a numeric array or both. Returns an array of associative or numeric
     *   arrays holding result rows.
     * @param    string   $type   This optional parameter indicates what type of array should be produced from the current
     *                              row data. The possible values for this parameter are 'num', 'assoc' or 'both'.
     *                              Default: 'assoc'.
     * @return   array
     * @access   public
     * @abstract
     */
    abstract public function fetchAll($type = 'assoc');

    /**
     * Gets the number of rows in a result
     * @return   int
     * @access   public
     * @abstract
     */
    abstract public function numRows();

    /**
     * Gets the number of fields in a result
     * @return   int
     * @access   public
     * @abstract
     */
    abstract public function numFields();

    /**
     * Frees the memory associated with the result
     * @return   void
     * @access   public
     * @abstract
     */
    abstract public function free();

}
