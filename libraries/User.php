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
            $this->setData($result->fetch(), [
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
     */
    public function getID() {
        return $this->get('id');
    }

    /**
     * Returns the username of the user
     * @return   string
     */
    public function getUsername() {
        return $this->get('username');
    }

    /**
     * Updates the username of the user
     * @param    string   $username   The new ussername
     */
    public function setUsername($username) {
        $this->set('username', $username);
    }

    /**
     * Returns the email address of the user
     * @return   string
     */
    public function getEmail() {
        return $this->get('email');
    }

    /**
     * Updates the email address of the user
     * @param    string   $email   The new email address
     */
    public function setEmail($email) {
        $this->set('email', $email);
    }

    /**
     * Checks if the given password matches the user's password hash
     * @param    string   $password   Thepassword to verify
     * @return   bool
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->get('password'));
    }

    /**
     * Updates the password of the user
     * @param    string   $password   The new password
     */
    public function setPassword($password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->set('password', $hash);
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
     * Updates the group of the user
     * @param    mixed   $group
     */
    public function setGroup($group) {
        $group = new UserGroup($group);
        $this->set('group', $group);
    }

    /**
     * Returns the value of the given user profile field. If no $key is given, the values of all fields
     *   are returned as an array.
     * @param    string   $key   The key of the profile field (optional)
     * @return   mixed
     */
    public function getProfile($key = null) {
        return isset($key) ? $this->getListItem('profile', $key) : $this->get('profile');
    }

    /**
     * Updates one or more items in the user profile
     * @param    mixed    $param1   The name of a single field (string) or pairs of names and values of multiple
     *                                fields (array in the format [name => value, ...]) to be updated
     * @param    mixed    $param2   The new value of the field to be updated (only if parameter 1 is used for
     *                                the field name)
     * @return   bool
     */
    public function setProfile($param1, $param2 = null) {
        if (is_array($param1)) {
            // Update multiple columns
            $this->setListItems('profile', $param1);
        } elseif (is_string($param1) && isset($param2)) {
            // Update a single column
            $this->setListItem('profile', $param1, $param2);
        } else {
            throw new InvalidArgumentException('The first parameter must be an array or a string together with the second parameter.');
        }
    }

    /**
     * Returns the last activity time of the user
     * @return   DateTime
     */
    public function getLastActive() {
        return $this->get('lastactive');
    }

    /**
     * Updates the last activity time of the user
     * @param    DateTime   $time   The new last activity time (Default: now)
     */
    public function setLastActive(DateTime $time = null) {
        $this->set('lastactive', $time ?: new DateTime);
    }

    /**
     * Checks if the user is online
     * @return   bool
     */
    public function isOnline() {
        $lastactive = $this->get('lastactive');
        $threshold = System::setting('Session:OnlineThreshold', 600);
        
        // Check if the last activity time is within the threshold
        return $lastactive->diff(new DateTime)->format('%s') <= $threshold;
    }

    /**
     * Checks if the user's group is hierarchically equal or superior to the given group
     * @param    mixed    $mingroup   Require at least this user group. Accepts ID (int) or name (string) of the group.
     * @return   bool
     */
    public function isAuthorized($mingroup) {
        $group = new UserGroup($this->get('group'));
        return $group->isAuthorized($mingroup);
    }

    /**
     * Updates the given columns in the database table
     * @param    array    $columns   Names and values of columns to be updated (Format: [name => value, ...])
     * @return   bool
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
     */
    public static function exists($id) {
        $sql = 'SELECT id FROM @PREFIX@users WHERE id = {0} LIMIT 1';
        $result = System::db()->query($sql, [$id]);
        
        return $result->hasRows();
    }

}
