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
 * View manager
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class View {
    
    /**
     * The loaded template object
     * @var      object
     * @access   private
     */
    private $_template;
    
    /**
     * The name of the theme to use (Default: value given in settings)
     * @var      string
     * @access   private
     * @static
     */
    private static $_theme;

    /**
     * The title of the page
     * @var      string
     * @access   private
     * @static
     */
    private static $_title;

    /**
     * The list of all head tags
     * @var      array
     * @access   private
     * @static
     */
    private static $_head_tags = array();

    /**
     * Generates a new template object
     * @param    string   $template   Name of the template to load
     * @return   void
     * @access   public
     */
    public function __construct($template) {
        $namespace = defined('WW_MODULE') ? WW_MODULE : null;
        $theme = self::getTheme();
        
        $this->_template = new Template($template, $namespace, $theme);
    }

    /**
     * Sets one or more template variables
     * @param    mixed    $param1   The name of the variable (string) or pairs of names and values of multiple
     *                                variables (array in the format [name => value, ...]) to be set
     * @param    mixed    $param2   The value of the variable (only if parameter 1 is used for the variable name)
     * @return   void
     * @access   public
     */
    public function set($param1, $param2 = null) {
        $this->_template->set($param1, $param2);
    }

    /**
     * Displays the view
     * @param    bool     $return   Return the rendered view
     * @return   string
     * @access   public
     * @static 
     */
    public function display() {
        $this->_template->display();
    }
    
    /**
     * Returns the used theme
     * @return   string
     * @access   public
     * @static
     */
    public static function getTheme() {
        return isset(self::$_theme) ? self::$_theme : System::setting('View:Theme', 'default');
    }
    
    /**
     * Sets the used theme
     * @param    string   $theme   The theme to use
     * @return   void
     * @access   public
     * @static
     */
    public static function setTheme($theme) {
        self::$_theme = (string) $theme;
    }
    
    /**
     * Returns the page title
     * @return   string
     * @access   public
     * @static
     */
    public static function getTitle($title = null, $append = true) {
        return self::$_title;
    }
    
    /**
     * Sets the page title
     * @param    string   $title    The text to set as title or append to the title
     * @param    bool     $append   Should the given text be appended to the currently set title? (Default: TRUE)
     * @return   void
     * @access   public
     * @static
     */
    public static function setTitle($title, $append = true) {
        if ($append && self::$_title != '') {
            self::$_title = $title.' &bull; '.self::$_title;
        } else {
            self::$_title = (string) $title;
        }
    }

    /**
     * Adds a meta tag to the head tags
     * @param    string   $name      The name of the meta tag
     * @param    string   $content   The value of the meta tag
     * @return   void
     * @access   public
     * @static
     */
    public static function addMetaTag($name, $content) {
        self::$_head_tags['meta'][] = array(
            'name'    => $name,
            'content' => $content
        );
    }

    /**
     * Adds a link tag to the head tags
     * @param    string   $rel    The relation attribute
     * @param    string   $url    The URL to the file
     * @param    string   $type   The type attribute
     * @return   void
     * @access   public
     * @static
     */
    public static function addLinkTag($rel, $url, $type) {
        self::$_head_tags['link'][] = array(
            'rel'  => $rel,
            'href' => $url,
            'type' => $type
        );
    }

    /**
     * Adds a stylesheet link to the head tags
     * @param    string   $url     The URL to the file
     * @param    string   $media   Only for this media types. Defaults to 'all'.
     * @return   void
     * @access   public
     * @static
     */
    public static function addCSS($url, $media = 'all') {
        self::$_head_tags['css'][] = array(
            'url'   => $url,
            'media' => $media
        );
    }

    /**
     * Adds a JavaScript to the head tags
     * @param    string   $url    The URL to the file
     * @param    string   $type   The content type of the script. Defaults to 'text/javascript'.
     * @return   void
     * @access   public
     * @static
     */
    public static function addScript($url, $type = 'application/javascript') {
        self::$_head_tags['script'][] = array(
            'url'  => $url,
            'type' => $type
        );
    }

    /**
     * Lists all registered head tags of a given group
     * @param    string   $group   The group of head tags to return
     * @return   array
     * @access   public
     * @static
     */
    public static function getHeadTags($group) {
        if (isset(self::$_head_tags[$group])) {
            return self::$_head_tags[$group];
        } else {
            return array();
        }
    }

}
