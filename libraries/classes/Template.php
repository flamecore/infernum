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
 * Template parser class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 * @author   Martin Lantzsch <martin@linux-doku.de>
 */
class Template {

    /**
     * The file path of the template to load (without '.tpl')
     * @var      string
     * @access   private
     */
    private $_filename;

    /**
     * All assigned template variables
     * @var      array
     * @access   private
     */
    private $_variables = array();
    
    /**
     * All assigned global template variables
     * @var      array
     * @access   private
     * @static
     */
    private static $_globalVars = array();

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
    private static $_headTags = array();

    /**
     * Generates a new template object
     * @param    string   $file     The file path of the template to load (without '.tpl.php')
     * @param    string   $module   The name of the module where the template file is loaded from. No module means that
     *                                the template is loaded from the global templates directory of the theme.
     * @param    string   $theme    The name of the theme where the template file is loaded from (optional)
     * @return   void
     * @access   public
     */
    public function __construct($file, $module = null, $theme = null) {
        if (!isset($theme))
            $theme = System::setting('main', 'theme');
        
        $this->_filename = self::locate($file, $module, $theme);
        
        // Set some common variables
        $this->set('SITE_URL', u());
    }

    /**
     * Sets a template variable
     * @param    string   $name    The name of the variable
     * @param    mixed    $value   The value of the variable
     * @return   void
     * @access   public
     */
    public function set($name, $value) {
        $this->_variables[$name] = $value;
    }

    /**
     * Sets a global template variable
     * @param    string   $name    The name of the variable
     * @param    mixed    $value   The value of the variable
     * @return   void
     * @access   public
     * @static
     */
    public static function setGlobal($name, $value) {
        self::$_globalVars[$name] = $value;
    }

    /**
     * Renders the loaded template
     * @param    bool     $output   Output the generated template? Defaults to TRUE.
     * @return   string
     * @access   public
     */
    public function render($output = true) {
        // Create a sandbox function to isolate the template
        $template = function() {
            // Import the defined variables
            extract(func_get_arg(1));
            
            // Load the template file and capture its output
            ob_start();
            include func_get_arg(0);
            return ob_get_clean();
        };
        
        // Render the template with defined variables
        $variables = array_merge(self::$_globalVars, $this->_variables);
        $content = $template($this->_filename, $variables);
        
        if ($output) {
            echo $content;
        } else {
            return $content;
        }
    }
    
    /**
     * Loads a template file from the given theme
     * @param    string   $file     The file path of the template to load (without '.tpl.php')
     * @param    string   $module   The name of the module where the template file is loaded from. No module means that
     *                                the template is loaded from the global templates directory of the theme.
     * @param    string   $theme    The name of the theme where the template file is loaded from (optional)
     * @return   string
     * @access   public
     * @static 
     */
    public static function locate($file, $module = null, $theme = null) {
        if (!isset($theme))
            $theme = System::setting('main', 'theme');
        
        if (isset($module)) {
            $filePath = WW_SITE_PATH.'/modules/'.$module.'/themes/'.$theme.'/templates/'.$file.'.tpl.php';
        } else {
            $filePath = WW_ENGINE_PATH.'/themes/'.$theme.'/templates/'.$file.'.tpl.php';
        }
        
        if (!file_exists($filePath))
            ww_error('Template file "'.$filePath.'" does not exist.', 'template.not_found');
        
        return $filePath;
    }

    /**
     * Returns the title
     * @return   string 
     * @access   public
     * @static
     */
    public static function getTitle() {
        return self::$_title;
    }

    /**
     * Sets the given text as title or appends it to the current title
     * @param    string   $title    The text to set as title or append to the title
     * @param    bool     $append   Should the given text be appended to the currently set title? Defaults to TRUE.
     * @return   void
     * @access   public
     * @static
     */
    public static function setTitle($title, $append = true) {
        if ($append && self::$_title != '') {
            self::$_title = $title.' &bull; '.self::$_title;
        } else {
            self::$_title = $title;
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
        self::$_headTags['meta'][] = array(
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
        self::$_headTags['link'][] = array(
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
        self::$_headTags['css'][] = array(
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
        self::$_headTags['script'][] = array(
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
        if (isset(self::$_headTags[$group])) {
            return self::$_headTags[$group];
        } else {
            return array();
        }
    }

}