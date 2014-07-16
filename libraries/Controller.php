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
 * Base module controller
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class Controller {
    
    /**
     * Executes the action with given arguments
     * @param    string   $action      The action name
     * @param    array    $arguments   Arguments as array
     * @return   bool
     * @access   public
     * @static
     */
    public static function execute($action, $arguments = false) {
        if (!self::actionExists($action))
            return false;
        
        if (is_array($arguments) && !empty($arguments)) {
            call_user_func_array([get_called_class(), 'action_'.$action], $arguments);
        } else {
            call_user_func([get_called_class(), 'action_'.$action]);
        }
        
        return true;
    }
    
    /**
     * Checks the existance of an action
     * @param    string   $action   The action name
     * @return   bool
     * @access   public
     * @static
     */
    public static function actionExists($action) {
        return method_exists(get_called_class(), 'action_'.$action);
    }

}
