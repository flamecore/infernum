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
 * Simple user session manager
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Session {
    
    /**
     * The session ID of the currently opened session
     * @var      string
     * @access   public
     * @static
     */
    public static $id = '';
    
    /**
     * The user who is assigned to the session
     * @var      User
     * @access   public
     * @static
     */
    public static $user;
    
    /**
     * The group of the assigned user
     * @var      UserGroup
     * @access   public
     * @static
     */
    public static $userGroup;
    
    /**
     * The stored session data
     * @var      array
     * @access   public
     * @static
     */
    public static $data = array();
    
    /**
     * The lifetime of a session in seconds
     * @var      int
     * @access   public
     * @static
     */
    public static $lifetime = 3600; // 60 minutes
    
    /**
     * Initializes the session system
     * @param    int      $lifetime   The lifetime of a session in seconds. Defaults to 3600.
     * @return   void
     * @access   public
     * @static
     */
    public static function init($lifetime = null) {
        if (isset($lifetime))
            self::$lifetime = $lifetime;
        
        // Clean up sessions table from expired sessions before proceeding
        self::cleanup();
        
        // Check if the user has a session cookie
        if ($sessionID = Http::getCookie('session')) {
            // Cookie found: Search database for session with given ID
            $sql = 'SELECT user, data FROM @PREFIX@sessions WHERE id = {0} AND expire > {1} LIMIT 1';
            $result = System::$db->query($sql, array($sessionID, date('Y-m-d H:i:s')));
            
            // Did we find the session so we can reuse it?
            if ($result->numRows() == 1) {
                // Session found: Assign ID of the resumed session
                self::$id = $sessionID;
                
                $sessionInfo = $result->fetchAssoc();
                
                if ($sessionInfo['user'] > 0) {
                    self::$user = new User($sessionInfo['user']);
                    self::$userGroup = new UserGroup(self::$user->data['group']);
                }
                
                // If there's some data, fetch and unserialize it
                if ($sessionInfo['data'] != '')
                    self::$data = unserialize($sessionInfo['data']);

                // Refresh the resumed session
                self::refresh();
                
                return;
            }
        }

        // No cookie or session found: Start a new session
        self::start();
    }
    
    /**
     * Starts a new session. Returns the session ID on success or FALSE on failure.
     * @return   string
     * @access   public
     * @static
     */
    public static function start() {
        if (self::$id != '')
            return false;
        
        // generate a session ID
        self::$id = self::_generateID();

        // set the session cookie
        Http::setCookie('session', self::$id, time()+self::$lifetime);

        // register the session in the database
        $sql = 'INSERT INTO @PREFIX@sessions (id, expire) VALUES({0}, {1})';
        System::$db->query($sql, array(self::$id, date('Y-m-d H:i:s', time()+self::$lifetime)));
        
        return self::$id;
    }
    
    /**
     * Destoroys the running (if the argument $sessionID is not set) or the given session
     * @param    string   $sessionID   The ID of the affected session (optional)
     * @return   bool
     * @access   public
     * @static
     */
    public static function destroy($sessionID = null) {
        if (!isset($sessionID)) {
            // No $sessionID given, assign ID of current session
            $sessionID = self::$id;
            
            self::$id = '';
            self::$user = null;
            self::$userGroup = null;
            
            Http::deleteCookie('session');
        }
        
        // Delete session from database
        $sql = 'DELETE FROM @PREFIX@sessions WHERE id = {0}';
        return System::$db->query($sql, array($sessionID));
    }
    
    /**
     * Refreshes the currently running (if the argument $sessionID is not set) or the given session
     * @param    string   $sessionID   The ID of the affected session (optional)
     * @return   void
     * @access   public
     * @static
     */
    public static function refresh($sessionID = null) {
        if (!isset($sessionID)) {
            // No $sessionID given, assign ID of this session
            $sessionID = self::$id;
        }
        
        // Update session in database
        $sql = 'UPDATE @PREFIX@sessions SET expire = {0} WHERE id = {1} LIMIT 1';
        System::$db->query($sql, array(date('Y-m-d H:i:s', time()+self::$lifetime), $sessionID));
        
        // Update the assigned user's last activity time
        if (self::isUserLogged()) {
            $sql = 'UPDATE @PREFIX@users SET lastactive = {0} WHERE id = {1} LIMIT 1';
            System::$db->query($sql, array(date('Y-m-d H:i:s'), self::$user->id));
        }
    }
    
    /**
     * Assigns a user to the currently running (if the argument $sessionID is not set) or the given session
     * @param    int      $userID      The ID of the user who belongs to the session
     * @param    string   $sessionID   The ID of the affected session (optional)
     * @return   bool
     * @access   public
     * @static
     */
    public static function assignUser($userID, $sessionID = null) {
        if (!isset($sessionID)) {
            // No $sessionID given, assign ID of this session
            $sessionID = self::$id;
            
            self::$user = new User($userID);
            self::$userGroup = new UserGroup(self::$user->data['group']);
        }
        
        // update session in database
        $sql = 'UPDATE @PREFIX@sessions SET user = {0} WHERE id = {1} LIMIT 1';
        return System::$db->query($sql, array($userID, $sessionID));
    }
    
    /**
     * Stores data to the currently running (if the argument $sessionID is not set) or the given session
     * @param    string   $key         The key of the data entry
     * @param    mixed    $value       The value of the data entry
     * @param    string   $sessionID   The ID of the affected session (optional)
     * @return   bool
     * @access   public
     * @static
     */
    public static function store($key, $value, $sessionID = null) {
        if (!isset($sessionID)) {
            // No $sessionID given, assign ID of this session
            $sessionID = self::$id;
            
            // Update session data locally
            self::$data[$key] = $value;
        }
        
        // Update session data in database
        $sql = 'UPDATE @PREFIX@sessions SET data = {0} WHERE id = {1} LIMIT 1';
        return System::$db->query($sql, array(serialize(self::$data), $sessionID));
    }
    
    /**
     * Deletes all expired sessions
     * @return   bool
     * @access   public
     * @static
     */
    public static function cleanup() {
        $sql = 'DELETE FROM @PREFIX@sessions WHERE expire <= {0}';
        return System::$db->query($sql, array(date('Y-m-d H:i:s')));
    }
    
    /**
     * Checks if the visitor is a registered user
     * @return   bool
     * @access   public
     * @static
     */
    public static function isUserLogged() {
        if (isset(self::$user) && is_a(self::$user, 'User')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Generates a unique session ID
     * @return   string
     * @access   private
     * @static
     */
    private static function _generateID() {
        return uniqid(time(), true);
    }
    
}
