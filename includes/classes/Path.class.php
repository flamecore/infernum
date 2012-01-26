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
 * This class parses the URL
 *
 * @author  Martin Lantzsch <martin@linux-doku.de>
 */
class Path {

    /**
     * The controller
     * @var      string
     * @access   readonly
     */
    private $controller;

    /**
     * The arguments
     * @var      string
     * @access   readonly
     */
    private $args = '';

    /**
     * Array of all extracted arguments
     * @var      array
     * @access   readonly
     */
    private $argsList = array();

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
     * Initializes the path parser
     * @param    string   $path      The path to parse
     * @param    string   $default   The default controller
     * @return   void
     * @access   public
     */
    public function __construct($path, $default = false) {
        // split the path into its parts
        $pathParts = explode('/', $path, 2);
        
        // get the controller
        if ($pathParts[0] != '') {
            $controller = strtolower(str_replace('-', '_', $pathParts[0]));
        } else {
            $controller = $default;
        }
        
        // get the arguments
        if (isSet($pathParts[1]) && $pathParts[1] != '') {
            $args = $pathParts[1];
            $argsList = explode('/', $args);
        } else {
            $args = '';
            $argsList = array();
        }
        
        // now we have all what we need
        $this->controller = $controller;
        $this->args = $args;
        $this->argsList = $argsList;
    }

}
