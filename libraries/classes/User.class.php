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
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class User {
    
    /**
     * The user's ID
     * @var      int
     * @access   public
     */
    public $id;
    
    /**
     * The data of the user
     * @var      array
     * @access   public
     */
    public $data = array();
    
    /**
     * Constructor
     * @param    mixed    $identifier   A unique value that identifies the user using the selector
     * @param    string   $selector     The field via which the user is selected
     * @return   void
     * @access   public
     */
    public function __construct($identifier, $selector = 'id') {
        // Check if the user is a registered user
        if (!empty($identifier)) {
            // Try to fetch user data by ID
            $sql = 'SELECT * FROM @PREFIX@users WHERE `'.$selector.'` = {0} LIMIT 1';
            $result = System::$db->query($sql, array($identifier));
            
            if ($result->numRows() == 1) {
                $userData = $result->fetchAssoc();

                $this->id = (int) $userData['id'];
                $this->data = $userData;
            } else {
                ww_error('User does not exist. ('.$selector.' = '.$identifier.')', 'user.not_found');
            }
        } else {
            ww_error('Invalid user identifier given.', 'user.invalid_identifier');
        }
    }

    /**
     * Checks if the user is online
     * @param    int      $threshold   The threshold in seconds at which a user is considered as logged off. Defaults
     *                                   to 600 seconds (= 10 minutes).
     * @return   bool
     * @access   public
     */
    public function isOnline($threshold = 600) {
        // Check if the last activity time is within the threshold
        $lastActive = strtotime($this->data['lastactive']);
        if (time() - $lastActive <= $threshold) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates the user data in the database
     * @param    mixed    $keyOrData   The name of a single column (string) or pairs of names and values of multiple
     *                                   columns (array in the format [name => value, ...]) to be updated
     * @param    mixed    $value       The new value of the column to be updated (only if parameter $keyOrData is used
     *                                   for the column name)
     * @return   bool
     * @access   public
     */
    public function setUserData($keyOrData, $value = null) {
        if (is_array($keyOrData)) {
            // Update multiple columns
            return System::$db->update('@PREFIX@users', $keyOrData, array(
                'where' => 'id = {0}',
                'vars' => array($this->id)
            ));
        } elseif (is_string($keyOrData) && isset($value)) {
            // Update a single column
            $data = array($keyOrData => $value);
            return System::$db->update('@PREFIX@users', $data, array(
                'where' => 'id = {0}',
                'vars' => array($this->id)
            ));
        }
        
        return false;
    }

}
