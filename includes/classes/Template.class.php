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
 * Template parser class
 *
 * @author  Martin Lantzsch <martin@linux-doku.de>
 */
class Template {

    /**
     * The name of the currently loaded template
     * @var     string
     * @access  private
     */
    private $_template;

    /**
     * The name of the module
     * @var     string
     * @access  private
     */
    private $_module;

    /**
     * All assigned template variables
     * @var     array
     * @access  private
     */
    private $_vars = array();

    /**
     * The title of the page
     * @var     string
     * @access  private
     * @static
     */
    private static $_title;

    /**
     * The list of all head tags
     * @var     array
     * @access  private
     * @static
     */
    private static $_headTags = array();

    /**
     * Generates a new template object
     * @param   string   $template  The name of the template to load (without '.tpl.php')
     * @param   mixed    $module    Load from this module
     * @return  void
     * @access  public
     */
    public function __construct($template, $module) {
        $this->_template = $template;
        $this->_module = $module;
    }

    /**
     * Sets (a) template variable(s)
     * @param   mixed   $name   The name of the variable. If an array is given, each of its items will be set as variable
     *                           where the key is the name.
     * @param   mixed   $value  The value of the variable. If you use an array in $name, this parameter can be omitted.
     * @return  void
     * @access  public
     */
    public function set($name, $value = null) {
        if (!is_array($name) && isset($value)) {
            // assign a single variable
            $this->_vars[$name] = $value;
        } else {
            // assign multiple variables
            foreach ($name as $name => $value) {
                $this->_vars[$name] = $value;
            }
        }
    }

    /**
     * Parses the template and outputs/returns it
     * @param   bool    $output  Output generated template? Defaults to TRUE.
     * @return  string
     * @access  public
     */
    public function parse($output = true) {
        // start output buffering
        ob_start();
        
        // go through all vars and define them as real ones
        foreach ($this->_vars as $varName => $varValue)
            $$varName = $varValue;
        
        // load the template file
        $theme = Settings::get('core', 'theme');
        $templateDir = HADES_DIR_THEMES.'/'.$theme.'/templates/'.$this->_module;
        include $templateDir.'/'.$this->_template.'.tpl.php';
        
        // output/return the template
        $content = ob_get_clean();
        if ($output) {
            echo $content;
        } else {
            return $content;
        }
    }

    /**
     * Sets the given text as title or appends it to the current title
     * @param   string  $title   The text to set as title or append to the title
     * @param   bool    $append  Should the given text be appended to the currently set title? Defaults to TRUE.
     * @return  void
     * @access  public
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
     * @param   string  $name     The name of the meta tag
     * @param   string  $content  The value of the meta tag
     * @param   bool    $once     Determines if the element should only be added once. Defaults to TRUE.
     * @return  void
     * @access  public
     * @static
     */
    public static function addMetaTag($name, $content, $once = true) {
        $entry = '<meta name="'.$name.'" content="'.$content.'" />';
        
        if ($once && in_array($entry, self::$_headTags[0]))
            return;
        
        self::$_headTags[0][] = $entry;
    }

    /**
     * Adds a link tag to the head tags
     * @param   string  $rel   The relation attribute
     * @param   string  $url   The URL to the file
     * @param   string  $type  The type attribute
     * @param   bool    $once  Determines if the element should only be added once. Defaults to TRUE.
     * @return  void
     * @access  public
     * @static
     */
    public static function addLinkTag($rel, $url, $type, $once = true) {
        $entry = '<link rel="'.$rel.'" href="'.$url.'" type="'.$type.'" />';
        
        if ($once && in_array($entry, self::$_headTags[1]))
            return;
        
        self::$_headTags[1][] = $entry;
    }

    /**
     * Adds a stylesheet link to the head tags
     * @param   string  $url    The URL to the file
     * @param   string  $media  Only for this media types. Defaults to 'all'.
     * @param   bool    $once   Determines if the element should only be added once. Defaults to TRUE.
     * @return  void
     * @access  public
     * @static
     */
    public static function addStylesheet($url, $media = 'all', $once = true) {
        $entry = '<link rel="stylesheet" href="'.$url.'" type="text/css" media="'.$media.'" />';
        
        if ($once && in_array($entry, self::$_headTags[2]))
            return;
        
        self::$_headTags[2][] = $entry;
    }

    /**
     * Adds a JavaScript to the head tags
     * @param   string  $url   The URL to the file
     * @param   bool    $once  Determines if the element should only be added once. Defaults to TRUE.
     * @return  void
     * @access  public
     * @static
     */
    public static function addJavaScript($url, $once = true) {
        $entry = '<script src="'.$url.'"></script>';
        
        if ($once && in_array($entry, self::$_headTags[3]))
            return;
        
        self::$_headTags[3][] = $entry;
    }

    /**
     * Lists all registered head tags sorted by group
     * @return  array
     * @access  public
     * @static
     */
    public static function getHeadTags() {
        $tagsList = array();
        
        // walk through all head tags groups and add their tags to the tags list
        foreach (self::$_headTags as $tagsGroup)
            $tagsList += $tagsGroup;
        
        return $tagsList;
    }

}
