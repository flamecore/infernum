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
 * Class for managing users
 *
 * @author Christian Neff <christian.neff@gmail.com>
 */
class User {
    
    /**
     * The user's ID (0 = guest/anonymous)
     * @var      int
     * @access   readonly
     */
    private $userID = 0;
    
    /**
     * The fetched user data
     * @var      array
     * @access   readonly
     */
    private $userData = array();
    
    /**
     * The UserGroup object containing data about user group
     * @var      UserGroup
     * @access   readonly
     */
    private $userGroup;

    /**
     * Getter for readonly properties
     * @return   mixed
     * @access   public
     */
    public function __get($varName) {
        if ($varName[0] != '_')
            return $this->$varName;
    }
    
    /**
     * Constructor
     * @param    mixed    $user     The ID (int) or username (string) of the user (0 = guest/anonymous)
     * @return   void
     * @access   public
     */
    public function __construct($user) {
        global $db;
        
        // check if the user is a registered user
        if (is_int($user) && $user > 0) {
            // try to fetch user data by ID
            $sql = 'SELECT * FROM @PREFIX@users WHERE id = {0} LIMIT 1';
            $result = $db->query($sql, array($user));
            if ($result->numRows() == 1) {
                $userData = $result->fetchAssoc();
            } else {
                throw new Exception('User with ID '.$user.' does not exist');
            }
        } elseif (is_string($user) && !empty($user)) {
            // try to fetch user data by username
            $sql = 'SELECT * FROM @PREFIX@users WHERE username = {0} LIMIT 1';
            $result = $db->query($sql, array($user));
            if ($result->numRows() == 1) {
                $userData = $result->fetchAssoc();
            } else {
                throw new Exception('User with username "'.$user.'" does not exist');
            }
        } else {
            $userData = array(
                'id'       => 0,
                'username' => Settings::get('core', 'guest_username'),
                'group'    => Settings::get('core', 'guest_group')
            );
        }

        // assign properties
        $this->userID = (int) $userData['id'];
        $this->userData = $userData;
        $this->userGroup = new UserGroup($userData['group']);
    }
    
    /**
     * Checks whether the user is a member or a guest
     * @return   bool
     * @access   public
     */
    public function isMember() {
        if ($this->userID > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks whether the user is online
     * @param    int      $threshold   The threshold in seconds at which a user is considered as logged off. Defaults
     *                                   to 600 seconds (= 10 minutes).
     * @return   bool
     * @access   public
     */
    public function isOnline($threshold = 600) {
        // guests ($_userID = 0) are always offline :)
        if ($this->userID == 0)
            return false;
        
        // check if the last activity time is within the threshold
        $lastActive = strtotime($this->userData['lastactive']);
        if (time() - $lastActive <= $threshold) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates the user data in the database
     * @param    $keyOrData   The name of a single column (string) or pairs of names and values of multiple
     *                          columns (array in the format [name => value, ...]) to be updated
     * @param    $value       The new value of the column to be updated, only if $keyOrData is used for the column name
     * @return   bool
     * @access   public
     */
    public function setUserData($keyOrData, $value = null) {
        global $db;
        
        if ($this->userID <= 0) {
            throw new Exception('Cannot update user data: Current user is a guest.');
            return false;
        }
    
        if (is_array($keyOrData)) {
            // update multiple columns
            $dataset = array();
            foreach ($keyOrData as $key => $value)
                $dataset[] = $key.' = {'.$key.'}';
            $sql = 'UPDATE @PREFIX@user SET '.implode(', ', $dataset).' WHERE id = {_id} LIMIT 1';
            $queryVars = $keyOrData + array('_id' => $this->userID);
            return $db->query($sql, $queryVars);
        } else {
            // update a single column
            $sql = 'UPDATE @PREFIX@user SET '.$keyOrData.' = {0} WHERE id = {1} LIMIT 1';
            return $db->query($sql, array($value, $this->userID));
        }
    }

}
