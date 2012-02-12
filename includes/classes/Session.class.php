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
     * The lifetime of a session in seconds
     * @var      int
     * @access   public
     */
    public $lifeTime = 3600; // 60 minutes
    
    /**
     * The session ID of the currently opened session
     * @var      string
     * @access   readonly
     */
    private $sessionID = '';
    
    /**
     * The ID of the user who is assigned to the session
     * @var      int
     * @access   readonly
     */
    private $assignedUser = 0;
    
    /**
     * The stored session data
     * @var      array
     * @access   readonly
     */
    private $data = array();

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
     * @param    int      $lifeTime   The lifetime of a session in seconds. Defaults to 3600.
     * @return   void
     * @access   public
     */
    public function __construct($lifeTime = 3600) {
        global $db;
        
        $this->lifeTime = $lifeTime;
    
        $sessionID = Http::getCookie('session');
        if ($sessionID !== false) {
            // find unexpired session matching session ID and fetch assigned user's ID
            $sql = 'SELECT user, data FROM @PREFIX@sessions WHERE id = {0} AND expire > {1} LIMIT 1';
            $result = $db->query($sql, array($sessionID, date('Y-m-d H:i:s')));
            if ($result->numRows() == 1) {
                $session = $result->fetchAssoc();
                
                // get session ID and assigned user
                $this->sessionID = $sessionID;
                $this->assignedUser = (int) $session['user'];
                
                // get stored session data, if available
                if ($session['data'] != '')
                    $this->data = unserialize($session['data']);

                // refresh the session
                $this->refresh();
            }
        } else {
            $this->start();
        }
    }
    
    /**
     * Destructor
     * @return   void
     * @access   public
     */
    public function __destruct() {
        $this->cleanup();
    }
    
    /**
     * Starts a new session. Returns the session ID on success or FALSE on failure.
     * @return   string
     * @access   public
     */
    public function start() {
        global $db;
        
        if ($this->sessionID != '')
            return false;
        
        // generate a session ID
        $sessionID = $this->_generateID();

        // set the session cookie
        Http::setCookie('session', $sessionID, time()+$this->lifeTime);

        // register the session in the database
        $sql = 'INSERT INTO @PREFIX@sessions (id, expire) VALUES({0}, {1})';
        $db->query($sql, array($sessionID, date('Y-m-d H:i:s', time()+$this->lifeTime)));

        // the session is now started, set session info
        $this->sessionID = $sessionID;
        
        return $sessionID;
    }
    
    /**
     * Destoroys the running (if the argument $sessionID is not set) or the given session
     * @param    string   $sessionID   The session ID to destroy. Optional.
     * @return   bool
     * @access   public
     */
    public function destroy($sessionID = null) {
        global $db;
        
        if (!isset($sessionID)) {
            // no $sessionID given, assign ID of current session
            $sessionID = $this->sessionID;
            
            // unset session info
            unset($this->sessionID);
            unset($this->assignedUser);
            
            // delete cookie
            Http::deleteCookie('session');
        }
        
        // delete session from database
        $sql = 'DELETE FROM @PREFIX@sessions WHERE id = {0}';
        return $db->query($sql, array($sessionID));
    }
    
    /**
     * Refreshes the currently running (if the argument $sessionID is not set) or the given session
     * @param    string   $sessionID   The session ID to refresh. Optional.
     * @return   bool
     * @access   public
     */
    public function refresh($sessionID = null) {
        global $db;
    
        if (!isset($sessionID)) {
            // no $sessionID given, assign ID of this session
            $sessionID = $this->sessionID;
        }
        
        // update session in database
        $sql = 'UPDATE @PREFIX@sessions SET expire = {0} WHERE id = {1} LIMIT 1';
        return $db->query($sql, array(date('Y-m-d H:i:s', time()+$this->lifeTime), $sessionID));
        
        // update the assigned user's last activity time
        if ($this->assignedUser > 0) {
            $sql = 'UPDATE @PREFIX@users SET lastactive = {0} WHERE id = {1} LIMIT 1';
            return $db->query($sql, array(date('Y-m-d H:i:s'), $this->assignedUser));
        }
    }
    
    /**
     * Delete expired sessions
     * @return   bool
     * @access   public
     */
    public function cleanup() {
        global $db;
    
        $sql = 'DELETE FROM @PREFIX@sessions WHERE expire <= {0}';
        return $db->query($sql, array(date('Y-m-d H:i:s')));
    }
    
    /**
     * Assigns a user to the currently running (if the argument $sessionID is not set) or the given session
     * @param    int      $userID      The ID of the user who belongs to the session
     * @param    string   $sessionID   The session ID to refresh. Optional.
     * @return   bool
     * @access   public
     */
    public function assignUser($userID, $sessionID = null) {
        global $db;
    
        if (!isset($sessionID)) {
            // no $sessionID given, assign ID of this session
            $sessionID = $this->sessionID;
            
            // assign given $userID to $this->userID
            $this->assignedUser = $userID;
        }
        
        // update session in database
        $sql = 'UPDATE @PREFIX@sessions SET user = {0} WHERE id = {1} LIMIT 1';
        return $db->query($sql, array($userID, $sessionID));
    }
    
    /**
     * Stores data to the currently running (if the argument $sessionID is not set) or the given session
     * @param    string   $key         The key of the data entry
     * @param    mixed    $value       The value of the data entry
     * @param    string   $sessionID   The session ID to refresh. Optional.
     * @return   bool
     * @access   public
     */
    public function store($key, $value, $sessionID = null) {
        global $db;
    
        if (!isset($sessionID)) {
            // no $sessionID given, assign ID of this session
            $sessionID = $this->sessionID;
            
            // update session data locally
            $this->data[$key] = $value;
        }
        
        // update session data in database
        $sql = 'UPDATE @PREFIX@sessions SET data = {0} WHERE id = {1} LIMIT 1';
        return $db->query($sql, array($this->data, $sessionID));
    }

    /**
     * Generates a unique session ID
     * @return   string
     * @access   private
     */
    private function _generateID() {
        return uniqID(time(), true);
    }
    
}
