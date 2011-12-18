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
 * Class for managing users
 *
 * @author Christian Neff <christian.neff@gmail.com>
 */
class User {
    
    /**
     * The fetched user data
     * @var     array
     * @access  readonly
     */
    private $info;

    /**
     * Getter for readonly properties
     * @return  mixed
     * @access  public
     */
    public function __get($varName) {
        if ($varName[0] != '_')
            return $this->$varName;
    }
    
    /**
     * Constructor
     * @param   mixed   $user  The ID (int) or the name (string) of the user
     * @return  void
     * @access  public
     */
    public function __construct($user) {
        global $db;
    
        // use which column to identify the user?
        if (is_int($user)) {
            $byColumn = 'id';
        } else {
            $byColumn = 'username';
        }

        // fetch user data for further usage
        $sql = 'SELECT * FROM #PREFIX#users WHERE '.$byColumn.' = {0} LIMIT 1';
        $result = $db->query($sql, array($user));
        if ($result->numRows() == 1)
            $this->info = $result->fetchRow();
    }

    /**
     * Updates the data of the current (if the argument $userID is not set) or the given user in the database
     * @param   $keyOrData  The name of a single column (string) or pairs of names and values of multiple
     *                        columns (array in the format [name => value, ...]) to be updated
     * @param   $value      The new value of the column to be updated, only if $keyOrData is used for the column name
     * @param   $userID     The ID of the user to be updated, optional
     * @access  public
     */
    public function updateData($keyOrData, $value = null, $userID = null) {
        global $db;
    
        if (is_array($keyOrData)) {
            // update multiple columns
            $dataset = array();
            foreach ($keyOrData as $key => $value)
                $dataset[] = $key.' = '.$value;
            $sql = 'UPDATE #PREFIX#user SET '.$dataset;
            if (isSet($userID))
                $sql .= ' WHERE id = '.$userID;
            $sql .= ' LIMIT 1';
            return $db->query($sql);
        } else {
            // update a single column
            $sql = 'UPDATE #PREFIX#user SET '.$keyOrData.' = '.$value;
            if (isSet($userID))
                $sql .= ' WHERE id = '.$userID;
            $sql .= ' LIMIT 1';
            return $db->query($sql);
        }
    }

}
