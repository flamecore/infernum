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
 * Object describing a user group
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class UserGroup {
    
    /**
     * The user group data
     * @var      array
     * @access   private
     */
    private $_data = [];
    
    /**
     * Constructor
     * @param    int      $identifier   The ID of the user group
     * @return   void
     * @access   public
     */
    public function __construct($identifier) {
        if (is_string($identifier)) {
            $selector = 'name';
        } elseif (is_int($identifier)) {
            $selector = 'id';
        } else {
            trigger_error('Invalid user group identifier given', E_USER_ERROR);
        }
        
        $sql = 'SELECT * FROM @PREFIX@usergroups WHERE '.$selector.' = {0} LIMIT 1';
        $result = System::db()->query($sql, [$identifier]);

        if ($result->numRows() == 1) {
            $data = $result->fetchAssoc();

            $this->_data['id'] = (int) $data['id'];
            $this->_data['name'] = $data['name'];
            $this->_data['title'] = $data['title'];
            $this->_data['accesslevel'] = (int) $data['accesslevel'];
        } else {
            throw new Exception('User group does not exist (id = '.$id.')');
        }
    }
    
    /**
     * Returns the groups's ID
     * @return   int
     * @access   public
     */
    public function getID() {
        return $this->_data['id'];
    }
    
    /**
     * Returns the name of the group
     * @return   string
     * @access   public
     */
    public function getName() {
        return $this->_data['name'];
    }
    
    /**
     * Returns the title of the group
     * @return   string
     * @access   public
     */
    public function getTitle() {
        return $this->_data['title'];
    }
    
    /**
     * Returns the access level of the group
     * @return   int
     * @access   public
     */
    public function getAccessLevel() {
        return $this->_data['accesslevel'];
    }

    /**
     * Checks if a user group with given ID exists
     * @param    int      $gid   The ID of the user group
     * @return   bool
     * @access   public
     * @static
     */
    public static function exists($gid) {
        $sql = 'SELECT id FROM @PREFIX@usergroups WHERE id = {0} LIMIT 1';
        $result = System::db()->query($sql, [$gid]);
        
        return $result->hasRows();
    }

}
