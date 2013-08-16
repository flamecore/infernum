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
 * Webwork's Exception class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class WebworkException extends Exception {
    
    /**
     * The Exception type
     * @var      string 
     * @access   protected
     */
    protected $type = 'main';

    /**
     * Constructor
     * @param    string   $message   The Exception message to throw
     * @param    string   $type      The Exception type
     * @param    int      $code      The Exception code
     * @access   public
     */
    public function __construct($message, $type, $code = 0) {
        parent::__construct($message, $code);

        $this->type = $type;
    }

    /**
     * Returns the Exception type
     * @return   string
     * @access   public
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Checks if the Exception is of a given type
     * @param    string   $comparestring   Comparison type (complete Exception type designation or first parts of it)
     * @return   bool
     * @access   public
     */
    public function type($comparestring) {
        $typeArray = explode('.', $this->type);
        $compareArray = explode('.', $comparestring);

        if ($compareArray < $typeArray)
            $typeArray = array_slice($typeArray, 0, count($compareArray));

        return $compareArray === $typeArray;
    }
    
}