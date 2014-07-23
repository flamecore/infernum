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
 * Object describing a registered user
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class User {
    
    /**
     * The data of the user
     * @var      array
     * @access   private
     */
    private $_data = [];
    
    /**
     * The profile info of the user
     * @var      array
     * @access   private
     */
    private $_profile = [];
    
    /**
     * Constructor
     * @param    mixed    $identifier   ID or username of the user
     * @return   void
     * @access   public
     */
    public function __construct($identifier) {
        if (is_string($identifier)) {
            $selector = 'username';
        } elseif (is_int($identifier)) {
            $selector = 'id';
        } else {
            trigger_error('Invalid user identifier given', E_USER_ERROR);
        }
        
        $sql = 'SELECT * FROM @PREFIX@users WHERE `'.$selector.'` = {0} LIMIT 1';
        $result = System::db()->query($sql, [$identifier]);

        if ($result->hasRows()) {
            $data = $result->fetchAssoc();
            
            $this->_data['id'] = (int) $data['id'];
            $this->_data['username'] = $data['username'];
            $this->_data['email'] = $data['email'];
            $this->_data['pwhash'] = $data['password'];
            $this->_data['group'] = (int) $data['group'];
            $this->_data['lastactive'] = strtotime($data['lastactive']);
            $this->_profile = json_decode($data['profile']);
        } else {
            throw new Exception('User does not exist ('.$selector.' = '.$identifier.')');
        }
    }
    
    /**
     * Returns the user's ID
     * @return   int
     * @access   public
     */
    public function getID() {
        return $this->_data['id'];
    }
    
    /**
     * Returns the username of the user
     * @return   string
     * @access   public
     */
    public function getUsername() {
        return $this->_data['username'];
    }
    
    /**
     * Returns the email address of the user
     * @return   string
     * @access   public
     */
    public function getEmail() {
        return $this->_data['email'];
    }
    
    /**
     * Checks if the given password matches the user's password hash stored in the database
     * @return   bool
     * @access   public
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->_data['pwhash']);
    }
    
    /**
     * Returns the ID of the group that the user belongs to
     * @return   int
     * @access   public
     */
    public function getGroupID() {
        return $this->_data['group'];
    }
    
    /**
     * Returns the value of the given user profile field. If no $key is given, the values of all fields
     *   are returned as an array.
     * @param    string   $key   The key of the profile field (optional)
     * @return   mixed
     * @access   public
     */
    public function getProfile($key = null) {
        return isset($key) ? $this->_profile[$key] : $this->_profile;
    }
    
    /**
     * Updates the username of the user
     * @return   bool
     * @access   public
     */
    public function setUsername($username) {
        $this->_data['username'] = $email;
        
        return $this->_update('username', $username);
    }
    
    /**
     * Updates the email address of the user
     * @return   bool
     * @access   public
     */
    public function setEmail($email) {
        $this->_data['email'] = $email;
        
        return $this->_update('email', $email);
    }
    
    /**
     * Updates the password of the user
     * @return   bool
     * @access   public
     */
    public function setPassword($password) {
        $pwhash = password_hash($password, PASSWORD_BCRYPT);
        
        $this->_data['pwhash'] = $pwhash;
        
        return $this->_update('password', $pwhash);
    }
    
    /**
     * Updates the user group
     * @return   bool
     * @access   public
     */
    public function setGroup($gid) {
        $gid = (int) $gid;
        
        if (!UserGroup::exists($gid)) {
            trigger_error('Can not set user group: Group does not exist', E_USER_WARNING);
            return false;
        }
        
        $this->_data['group'] = $gid;
        
        return $this->_update('group', $gid);
    }

    /**
     * Updates user profile in the database
     * @param    mixed    $param1   The name of a single column (string) or pairs of names and values of multiple
     *                                columns (array in the format [name => value, ...]) to be updated
     * @param    mixed    $param2   The new value of the column to be updated (only if parameter 1 is used for
     *                                the column name)
     * @return   bool
     * @access   public
     */
    public function setProfile($param1, $param2 = null) {
        if (is_array($param1)) {
            // Update multiple columns
            foreach ($param1 as $key => $value) {
                $this->_profile[$key] = $value;
            }
        } elseif (is_string($param1) && isset($param2)) {
            // Update a single column
            $this->_profile[$param1] = $param2;
        }
        
        return $this->_update('profile', json_encode($this->_profile));
    }

    /**
     * Checks if the user is online
     * @return   bool
     * @access   public
     */
    public function isOnline() {
        $lastactive = $this->_data['lastactive'];
        $threshold = System::setting('Session:OnlineThreshold', 600);
        
        // Check if the last activity time is within the threshold
        return time() - $lastactive <= $threshold;
    }
    
    /**
     * Updates the given column in the user database table
     * @return   bool
     * @access   private
     */
    private function _update($column, $value) {
        return System::db()->update('@PREFIX@users', [$column => $value], [
            'where' => 'id = {0}',
            'vars' => [$this->_data['id']]
        ]);
    }
    
    /**
     * Checks if a user with given ID exists
     * @param    int      $uid   The ID of the user
     * @return   bool
     * @access   public
     * @static
     */
    public static function exists($uid) {
        $sql = 'SELECT id FROM @PREFIX@users WHERE id = {0} LIMIT 1';
        $result = System::db()->query($sql, [$uid]);
        
        return $result->hasRows();
    }

}
