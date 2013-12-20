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
 * Simple access control manager
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class AccessManager {
    
    /**
     * The accesslevel of the user
     * @var      int
     * @access   private
     * @static
     */
    private static $_accesslevel;

    /**
     * Initializes the access control system
     * @return   void
     * @access   public
     * @static
     */
    public static function init() {
        $groupid = SessionManager::getUser()->getGroupID();
        
        $group = new UserGroup($groupid);
        self::$_accesslevel = $group->getAccessLevel();
    }
    
    /**
     * Checks if user has at least the access level of the given group
     * @param    string   $mingroup   At least this group
     * @return   bool
     * @static
     */
    public static function checkLevel($mingroup) {
        $mingroup = new UserGroup($mingroup);
        $minlevel = $mingroup->getAccessLevel();
        
        return self::$_accesslevel >= $minlevel;
    }
    
    /**
     * Require minimum access level
     * @param    string   $mingroup   At least this group
     * @return   void
     * @static
     */
    public static function requireLevel($mingroup) {
        if (!self::checkLevel($mingroup))
            error(403);
    }
    
}