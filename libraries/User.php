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
class User extends DatabaseRecord {
    
    /**
     * Fetches the data of the user
     * @param    mixed    $identifier   The ID (int) or username (string) of the user
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
        
        $sql = sprintf('SELECT * FROM @PREFIX@users WHERE `%s` = {0} LIMIT 1', $selector);
        $result = System::db()->query($sql, [$identifier]);

        if ($result->hasRows()) {
            $this->setData($result->fetchAssoc(), [
                'id' => 'int',
                'username' => 'string',
                'email' => 'string',
                'password' => 'string',
                'group' => 'int',
                'lastactive' => 'datetime',
                'profile' => 'array'
            ]);
        } else {
            throw new Exception(sprintf('User does not exist (%s = %s)', $selector, $identifier));
        }
    }
    
    /**
     * Returns the user's ID
     * @return   int
     * @access   public
     */
    public function getID() {
        return $this->get('id');
    }
    
    /**
     * Returns the username of the user
     * @return   string
     * @access   public
     */
    public function getUsername() {
        return $this->get('username');
    }
    
    /**
     * Returns the email address of the user
     * @return   string
     * @access   public
     */
    public function getEmail() {
        return $this->get('email');
    }
    
    /**
     * Checks if the given password matches the user's password hash stored in the database
     * @return   bool
     * @access   public
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->get('password'));
    }
    
    /**
     * Returns the ID of the group that the user belongs to
     * @return   int
     * @access   public
     */
    public function getGroupID() {
        return $this->get('group');
    }
    
    /**
     * Returns the value of the given user profile field. If no $key is given, the values of all fields
     *   are returned as an array.
     * @param    string   $key   The key of the profile field (optional)
     * @return   mixed
     * @access   public
     */
    public function getProfile($key = null) {
        return isset($key) ? $this->getListItem('profile', $key) : $this->get('profile');
    }
    
    /**
     * Updates the username of the user
     * @return   bool
     * @access   public
     */
    public function setUsername($username) {
        return $this->set('username', $username);
    }
    
    /**
     * Updates the email address of the user
     * @return   bool
     * @access   public
     */
    public function setEmail($email) {
        return $this->set('email', $email);
    }
    
    /**
     * Updates the password of the user
     * @return   bool
     * @access   public
     */
    public function setPassword($password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        return $this->set('password', $hash);
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
        
        return $this->set('group', $gid);
    }

    /**
     * Updates one or more items in the user profile
     * @param    mixed    $param1   The name of a single field (string) or pairs of names and values of multiple
     *                                fields (array in the format [name => value, ...]) to be updated
     * @param    mixed    $param2   The new value of the field to be updated (only if parameter 1 is used for
     *                                the field name)
     * @return   bool
     * @access   public
     */
    public function setProfile($param1, $param2 = null) {
        if (is_array($param1)) {
            // Update multiple columns
            return $this->setListItems('profile', $param1);
        } elseif (is_string($param1) && isset($param2)) {
            // Update a single column
            return $this->setListItem('profile', $param1, $param2);
        } else {
            throw new InvalidArgumentException('The first parameter must be an array or a string together with the second parameter.');
        }
    }

    /**
     * Checks if the user is online
     * @return   bool
     * @access   public
     */
    public function isOnline() {
        $lastactive = $this->get('lastactive');
        $threshold = System::setting('Session:OnlineThreshold', 600);
        
        // Check if the last activity time is within the threshold
        return time() - $lastactive->getTimestamp() <= $threshold;
    }
    
    /**
     * Updates the given columns in the database table
     * @param    array    $columns   Names and values of columns to be updated (Format: [name => value, ...])
     * @return   bool
     * @access   protected
     */
    protected function update($columns) {
        return System::db()->update('@PREFIX@users', $columns, [
            'where' => 'id = {0}',
            'vars' => [$this->get('id')]
        ]);
    }
    
    /**
     * Checks if a user with given ID exists
     * @param    int      $id   The ID of the user
     * @return   bool
     * @access   public
     * @static
     */
    static public function exists($id) {
        $sql = 'SELECT id FROM @PREFIX@users WHERE id = {0} LIMIT 1';
        $result = System::db()->query($sql, [$id]);
        
        return $result->hasRows();
    }

}
