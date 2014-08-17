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
     * @param    array    $arguments   The arguments as array (optional)
     * @return   bool
     */
    final public function run($action, Array $arguments = null) {
        if (!$this->actionExists($action))
            return false;
        
        if (isset($arguments) && !empty($arguments)) {
            call_user_func_array([$this, 'action_'.$action], $arguments);
        } else {
            call_user_func([$this, 'action_'.$action]);
        }
        
        return true;
    }
    
    /**
     * Checks whether an action exists
     * @param    string   $action   The action name
     * @return   bool
     */
    final public function actionExists($action) {
        return method_exists($this, 'action_'.$action);
    }

}
