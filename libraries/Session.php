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
class Session extends DatabaseRecord {
    
    /**
     * Fetches the data of the session
     * @param    string   $identifier   The ID of the session
     * @return   void
     * @access   public
     */
    public function __construct($identifier) {
        $sql = 'SELECT * FROM @PREFIX@sessions WHERE id = {0} AND expire > {1} LIMIT 1';
        $result = System::db()->query($sql, [$identifier, date('Y-m-d H:i:s')]);
        
        if ($result->hasRows()) {
            $info = $result->fetchAssoc();
            
            $this->data = array(
                'id' => $info['id'],
                'lifetime' => $info['lifetime'],
                'user' => $info['user'] > 0 ? $info['user'] : null,
                'data' => !empty($info['data']) ? unserialize($info['data']) : null
            );
        } else {
            throw new Exception('Session does not exist (id = '.$identifier.')');
        }
    }
    
    /**
     * Refreshes the session
     * @return   void
     * @access   public
     */
    public function refresh() {
        $expire = time() + $this->get('lifetime');
        $this->set('expire', date('Y-m-d H:i:s', $expire));
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
        
        $this->set('user', $uid);
    }
    
    /**
     * Reads data from the currently running session
     * @param    string   $key   The key of the data entry
     * @return   mixed
     * @access   public
     */
    public function read($key) {
        return $this->getListItem('data', $key);
    }
    
    /**
     * Stores data to the session
     * @param    string   $key     The key of the data entry
     * @param    mixed    $value   The value of the data entry
     * @return   bool
     * @access   public
     */
    public function store($key, $value) {
        return $this->setListItem('data', $key, $value);
    }

    /**
     * Sets the lifetime of the session
     * @param    int      $lifetime   The new session lifetime in seconds
     * @return   void
     * @access   public
     */
    public function setLifetime($lifetime) {
        $this->setMultiple([
            'lifetime' => $lifetime,
            'expire' => time() + $lifetime
        ]);
    }

    /**
     * Destoroys the session
     * @return   bool
     * @access   public
     */
    public function destroy() {
        $sql = 'DELETE FROM @PREFIX@sessions WHERE id = {0}';
        return System::db()->query($sql, [$this->_sid]);
        
        unset($this->data);
        $this->close();
    }
    
    /**
     * Returns the ID of the session
     * @return   string
     * @access   public
     */
    public function getID() {
        return $this->get('id');
    }
    
    /**
     * Returns the ID of the user who is assigned to the session. FALSE is returned if no user is logged.
     * @return   string
     * @access   public
     */
    public function getUserID() {
        return $this->get('user');
    }
    
    /**
     * Updates the given columns in the database table
     * @param    array    $columns   Names and values of columns to be updated (Format: [name => value, ...])
     * @return   bool
     * @access   protected
     */
    protected function update($columns) {
        return System::db()->update('@PREFIX@sessions', $columns, [
            'where' => 'id = {0}',
            'vars' => [$this->get('id')]
        ]);
    }
    
    /**
     * Checks wheter or not the session with given ID exists
     * @param    string   $id   The ID of the session
     * @return   bool
     * @access   public
     * @static
     * @abstract
     */
    public static function exists($id) {
        $sql = 'SELECT id FROM @PREFIX@sessions WHERE id = {0} LIMIT 1';
        $result = System::db()->query($sql, [$id]);
        
        return $result->hasRows();
    }
    
}