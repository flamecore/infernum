<?php
/**
 * HadesLite
 * Copyright (C) 2011 Hades Project
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
 * @package     HadesLite
 * @version     0.1-dev
 * @link        http://hades.iceflame.net
 * @license     ISC License (http://www.opensource.org/licenses/ISC)
 */

/**
 * This class parses the URL
 *
 * @author  Martin Lantzsch <martin@linux-doku.de>
 */
class Path {

    /**
     * All path parts
     * @var     array
     * @access  readonly
     */
    private $parts;

    /**
     * The controller
     * @var     string
     * @access  readonly
     */
    private $controller;

    /**
     * The action
     * @var     string
     * @access  readonly
     */
    private $action = array();

    /**
     * All extracted params
     * @var     array
     * @access  readonly
     */
    private $params = array();

    /**
     * Getter for readonly properties
     * @return  mixed
     * @access  public
     */
    public function __get($varName) {
        if ($varName[0] != '_')
            return $this->$varName;
    }

    /**
     * Initializes the path parser
     * @param   string  $path  The path to parse
     * @return  void
     * @access  public
     */
    public function __construct($path) {
        // split the path into its parts
        $pathParts = explode('/', $path);
        
        // get the controller
        if ($pathParts[0] != '') {
            $controller = strtolower(str_replace('-', '_', $pathParts[0]));
        } else {
            $controller = Settings::get('core', 'frontpage');
        }
        
        // get the action
        if ($pathParts[1] != '') {
            $action = strtolower(str_replace('-', '_', $pathParts[1]));
        } else {
            $action = 'index';
        }
        
        // get the params
        $params = array_slice($pathParts, 2);
        
        // now we have all what we need
        $this->parts = $pathParts;
        $this->controller = $controller;
        $this->action = $action;
        $this->params = $params;
    }

    /**
     * Generates a URL from given controller, action and arguments
     * @param   string  $controller  The controller where the link goes to
     * @param   string  $action      The action where the link goes to
     * @param   array   $args        The arguments to use, optional
     * @return  string
     * @static
     */
    public static function build($controller, $action, $args = null) {
        $url = '?p='.$controller.'/'.$action;
        if (is_array($args))
            $url .= '/'.implode('/', $args);
        return $url;
    }

}
