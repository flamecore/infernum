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
 * Object describing an open user session
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Session {
    
    /**
     * The ID of the opened session
     * @var      string
     * @access   private
     */
    private $_sid;
    
    /**
     * The ID of the user who is assigned to the session
     * @var      int
     * @access   private
     */
    private $_uid;
    
    /**
     * The stored session data
     * @var      array
     * @access   private
     */
    private $_data = [];
    
    /**
     * The lifetime of the session in seconds
     * @var      int
     * @access   private
     */
    private $_lifetime = 3600;
    
    /**
     * Constructor
     * @param    int      $sid   The ID of the session
     * @return   void
     * @access   public
     */
    public function __construct($sid) {
        $sql = 'SELECT * FROM @PREFIX@sessions WHERE id = {0} AND expire > {1} LIMIT 1';
        $result = System::db()->query($sql, [$sid, date('Y-m-d H:i:s')]);
        
        if ($result->numRows() == 1) {
            $info = $result->fetchAssoc();
            
            $this->_sid = $info['id'];
            $this->_lifetime = $info['lifetime'];
            
            if ($info['user'] > 0)
                $this->_uid = $info['user'];
            
            if ($info['data'] != '')
                $this->data = unserialize($info['data']);
        } else {
            throw new Exception('Session does not exist (id = '.$sid.')');
        }
    }
    
    /**
     * Refreshes the session
     * @return   void
     * @access   public
     */
    public function refresh() {
        $sql = 'UPDATE @PREFIX@sessions SET expire = {0} WHERE id = {1} LIMIT 1';
        System::db()->query($sql, [date('Y-m-d H:i:s', time()+$this->_lifetime), $this->_sid]);
    }
    
    /**
     * Assigns a user to the session
     * @param    int      $uid   The ID of the user to assign
     * @return   bool
     * @access   public
     */
    public function assignUser($uid) {
        $uid = (int) $uid;
        
        if (!User::exists($uid)) {
            trigger_error('Can not assign user to session: User does not exist', E_USER_WARNING);
            return false;
        }
        
        $this->_uid = $uid;
        
        $sql = 'UPDATE @PREFIX@sessions SET user = {0} WHERE id = {1} LIMIT 1';
        return System::db()->query($sql, [$uid, $this->_sid]);
    }
    
    /**
     * Reads data from the currently running session
     * @param    string   $key   The key of the data entry
     * @return   mixed
     * @access   public
     */
    public function read($key) {
        return isset($this->_data[$key]) ? $this->_data[$key] : false;
    }
    
    /**
     * Stores data to the session
     * @param    string   $key     The key of the data entry
     * @param    mixed    $value   The value of the data entry
     * @return   bool
     * @access   public
     */
    public function store($key, $value) {
        $this->_data[$key] = $value;
        
        $sql = 'UPDATE @PREFIX@sessions SET data = {0} WHERE id = {1} LIMIT 1';
        return System::db()->query($sql, [serialize($this->_data), $this->_sid]);
    }

    /**
     * Sets the lifetime of the session
     * @param    int      $time   The new session lifetime in seconds. Defaults to 3600.
     * @return   void
     * @access   public
     */
    public function setLifetime($time = 3600) {
        $this->_lifetime = $time;
        
        $sql = 'UPDATE @PREFIX@sessions SET lifetime = {0}, expire = {1} WHERE id = {2} LIMIT 1';
        System::db()->query($sql, [$this->_lifetime, time()+$this->_lifetime, $this->_sid]);
    }

    /**
     * Destoroys the session
     * @return   bool
     * @access   public
     */
    public function destroy() {
        $sql = 'DELETE FROM @PREFIX@sessions WHERE id = {0}';
        return System::db()->query($sql, [$this->_sid]);
        
        $this->_sid = null;
        $this->_userid = null;
    }
    
    /**
     * Returns the ID of the session
     * @return   string
     * @access   public
     */
    public function getSID() {
        return $this->_sid;
    }
    
    /**
     * Returns the ID of the user who is assigned to the session. FALSE is returned if no user is logged.
     * @return   string
     * @access   public
     */
    public function getUserID() {
        return isset($this->_uid) ? $this->_uid : false;
    }
    
}