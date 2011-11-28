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
     * The name of the controller
     * @var     string
     * @access  private
     */
    private $_controller;

    /**
     * All assigned template variables
     * @var     array
     * @access  private
     */
    private $_vars = array();

    /**
     * The title of the page
     * @var     string
     * @access  public
     * @static
     */
    public static $pageTitle;

    /**
     * The list of all head tags
     * @var     array
     * @access  private
     * @static
     */
    private static $_headTags = array();

    /**
     * Generates a new template object
     * @param   string   $template    The name of the template to load (without '.tpl.php')
     * @param   mixed    $controller  Load from this module
     * @return  void
     * @access  public
     */
    public function __construct($template, $controller) {
        $this->_template = $template;
        $this->_controller = $controller;
    }

    /**
     * Sets (a) template variable(s)
     * @param   mixed   $name   The name of the variable. If an array is given, each of its items will be set as variable
     *                           where the key is the name.
     * @param   mixed   $value  The value of the variable. If you use an array in $name, this parameter can be omitted.
     * @return  void
     * @access  public
     */
    public function set($name, $value = false) {
        if (!is_array($name) && $value) {
            // assign var to array
            $this->_vars[$name] = $value;
        } else {
            // assign all array vars
            foreach ($name as $name => $value) {
                $this->_vars[$name] = $value;
            }
        }
    }

    /**
     * Parses the template and returns it
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
        $templateDir = HADES_DIR_THEMES.'/'.$theme.'/templates/'.$this->_controller;
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
     * Imports the given template of the selected theme
     * @param   $template  The template to import
     * @return  void
     * @access  public
     * @static
     */
    public static function import($template) {
        $theme = Settings::get('core', 'theme');
        $templateDir = HADES_DIR_THEMES.'/'.$theme.'/templates';
        include $templateDir.'/'.$template.'.tpl.php';
    }

    /**
     * Appends the given text to the page title
     * @param   string  $title  The text to append
     * @return  void
     * @access  public
     * @static
     */
    public static function setPageTitle($title) {
        self::$pageTitle = $title.' | '.self::$pageTitle;
    }

    /**
     * Adds a CSS file to the list
     * @param   string  $module  Load from this module...
     * @param   string  $file    ... this file
     * @param   string  $media   Only for this media types. Defaults to 'all'.
     * @param   bool    $once    Determines if the element should only be added once. Defaults to TRUE.
     * @return  bool
     * @access  public
     * @static
     */
    public static function addCSS($module, $file, $media = 'all', $once = true) {
        $entry = array($module, $file, $media);
        if ($once && in_array($entry, self::$_headTags['css'])) {
            return false;
        }
        self::$_headTags['css'][] = $entry;
        return true;
    }

    /**
     * Adds a JavaScript file to the list
     * @param   string  $module  Load from this module...
     * @param   string  $file    ... this file
     * @param   bool    $once    Determines if the element should only be added once. Defaults to TRUE.
     * @return  bool
     * @access  public
     * @static
     */
    public static function addJS($module, $file, $once = true) {
        $entry = array($module, $file);
        if ($once && in_array($entry, self::$_headTags['js'])) {
            return false;
        }
        self::$_headTags['js'][] = $entry;
        return true;
    }

    /**
     * Gets all registerd head tags
     * @param   string  $type  The type of the head tags
     * @return  array
     * @access  public
     * @static
     */
    public static function listHeadTags($type) {
        if (isSet(self::$_headTags[$type])) {
            return self::$_headTags[$type];
        } else {
            return array();
        }
    }

}
