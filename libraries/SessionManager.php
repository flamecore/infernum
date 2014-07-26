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
class SessionManager {
    
    /**
     * The ID of the currently running session
     * @var      string
     * @access   private
     * @static
     */
    private static $_sid;
    
    /**
     * The Session object of the currently running session
     * @var      Session
     * @access   private
     * @static
     */
    private static $_session;
    
    /**
     * The user who is assigned to the currently running session
     * @var      User
     * @access   private
     * @static
     */
    private static $_user;
    
    /**
     * Initializes the session system
     * @return   void
     * @access   public
     * @static
     */
    public static function init() {
        // Clean up sessions table from expired sessions before proceeding
        self::cleanup();
        
        try {
            // Check if the user has a session cookie
            if (self::$_sid = Util::getCookie('session')) {
                // Cookie found: Try to reopen the session
                self::$_session = new Session(self::$_sid);

                self::refresh();
            } else {
                // No cookie found: Theres no session to reopen
                throw new Exception();
            }
        } catch (Exception $e) {
            // Create a new session with a unique ID
            $sid = uniqid(time(), true);

            // Register the new session in the database
            $expire = time() + System::setting('Session:Lifetime', 3600);
            
            $sql = 'INSERT INTO @PREFIX@sessions (id, expire) VALUES({0}, {1})';
            System::db()->query($sql, array($sid, date('Y-m-d H:i:s', $expire)));

            // Set the session cookie
            Util::setCookie('session', $sid);

            self::$_sid = $sid;
            self::$_session = new Session(self::$_sid);
        }
    }
    
    /**
     * Refreshes the currently running session
     * @return   void
     * @access   public
     * @static
     */
    public static function refresh() {
        if (!isset(self::$_sid))
            trigger_error('SessionManager is not yet initialized', E_USER_ERROR);
        
        self::$_session->refresh();
        
        // Update the assigned user's last activity time
        if (self::isUserLogged()) {
            $sql = 'UPDATE @PREFIX@users SET lastactive = {0} WHERE id = {1} LIMIT 1';
            System::db()->query($sql, array(date('Y-m-d H:i:s'), self::$_user->id));
        }
    }
    
    /**
     * Assigns a user to the currently running session
     * @param    int      $uid   The ID of the user who belongs to the session
     * @return   bool
     * @access   public
     * @static
     */
    public static function assignUser($uid) {
        if (!isset(self::$_sid))
            trigger_error('SessionManager is not yet initialized', E_USER_ERROR);
        
        $assigned = self::$_session->assignUser($uid);
        
        if (!$assigned)
            return false;
        
        self::$_user = new User($uid);
        
        return true;
    }
    
    /**
     * Reads data from the currently running session
     * @param    string   $key   The key of the data entry
     * @return   mixed
     * @access   public
     * @static
     */
    public static function read($key) {
        if (!isset(self::$_sid))
            trigger_error('SessionManager is not yet initialized', E_USER_ERROR);
        
        return self::$_session->read($key);
    }
    
    /**
     * Stores data to the currently running session
     * @param    string   $key     The key of the data entry
     * @param    mixed    $value   The value of the data entry
     * @return   bool
     * @access   public
     * @static
     */
    public static function store($key, $value) {
        if (!isset(self::$_sid))
            trigger_error('SessionManager is not yet initialized', E_USER_ERROR);
        
        return self::$_session->store($key, $value);
    }

    /**
     * Sets the lifetime of the session
     * @param    int      $time   The new session lifetime in seconds (Defaults to 3600)
     * @return   void
     * @access   public
     * @static
     */
    public static function setLifetime($time = 3600) {
        if (!isset(self::$_sid))
            trigger_error('SessionManager is not yet initialized', E_USER_ERROR);
        
        self::$_session->setLifetime($time);
    }
    
    /**
     * Destoroys the currently running session
     * @return   bool
     * @access   public
     * @static
     */
    public static function destroy() {
        if (!isset(self::$_sid))
            trigger_error('SessionManager is not yet initialized', E_USER_ERROR);
        
        self::$_sid = null;
        self::$_user = null;
        self::$_usergroup = null;

        Util::deleteCookie('session');
        
        return self::$_session->destroy();
    }
    
    /**
     * Returns the ID of the currently running session
     * @return   string
     * @access   public
     * @static
     */
    public static function getSID() {
        if (!isset(self::$_sid))
            trigger_error('SessionManager is not yet initialized', E_USER_ERROR);
        
        return self::$_sid;
    }

    /**
     * Returns the User object of the logged in user. FALSE is returned if no user is logged.
     * @return   User
     * @access   public
     * @static
     */
    public static function getUser() {
        if (!isset(self::$_sid))
            trigger_error('SessionManager is not yet initialized', E_USER_ERROR);
        
        return self::isUserLogged() ? self::$_user : false;
    }
    
    /**
     * Checks if the visitor is a registered user
     * @return   bool
     * @access   public
     * @static
     */
    public static function isUserLogged() {
        if (!isset(self::$_sid))
            trigger_error('SessionManager is not yet initialized', E_USER_ERROR);
        
        return isset(self::$_user) && is_a(self::$_user, 'User');
    }
    
    /**
     * Deletes all expired sessions
     * @return   bool
     * @access   public
     * @static
     */
    public static function cleanup() {
        $sql = 'DELETE FROM @PREFIX@sessions WHERE expire <= {0}';
        return System::db()->query($sql, array(date('Y-m-d H:i:s')));
    }
    
}