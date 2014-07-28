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
     * The full filename of the template to load (without file extension)
     * @var      string
     * @access   private
     */
    private $filename;

    /**
     * All assigned template variables
     * @var      array
     * @access   private
     */
    private $variables = array();
    
    /**
     * All assigned global template variables
     * @var      array
     * @access   private
     * @static
     */
    static private $globals = array();

    /**
     * Generates a new template object
     * @param    string   $name     Name of the template to load (without file extension)
     * @param    string   $module   Name of the module where the template file is loaded from. No module means that
     *                                the template is loaded from the global templates directory of the theme.
     * @param    string   $theme    Name of the theme where the template file is loaded from (optional)
     * @return   void
     * @access   public
     */
    public function __construct($name, $module = null, $theme = null) {
        if (!isset($theme))
            $theme = View::getTheme();
        
        $this->filename = self::locate($name, $module, $theme);
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
        if (is_array($param1)) {
            // Set multiple variables
            array_merge($this->variables, $param1);
        } elseif (is_string($param1) && isset($param2)) {
            // Set a single variable
            $this->variables[$param1] = $param2;
        }
    }

    /**
     * Sets one or more global template variables
     * @param    mixed    $param1   The name of the variable (string) or pairs of names and values of multiple
     *                                variables (array in the format [name => value, ...]) to be set
     * @param    mixed    $param2   The value of the variable (only if parameter 1 is used for the variable name)
     * @return   void
     * @access   public
     * @static
     */
    public static function setGlobal($param1, $param2 = null) {
        if (is_array($param1)) {
            // Set multiple variables
            array_merge(self::$globals, $param1);
        } elseif (is_string($param1) && isset($param2)) {
            // Set a single variable
            self::$globals[$param1] = $param2;
        }
    }

    /**
     * Renders the loaded template
     * @return   string
     * @access   public
     */
    public function render() {
        // Create a sandbox function to isolate the template
        $sandbox = function () {
            extract(func_get_arg(1));
            
            ob_start();
            include func_get_arg(0);
            return ob_get_clean();
        };
        
        // Render the template with defined variables
        $variables = array_merge(self::$globals, $this->variables);
        
        return $sandbox($this->filename, $variables);
    }
    
    /**
     * Displays the loaded template
     * @return   void
     * @access   public
     */
    public function display() {
        echo $this->render();
    }
    
    /**
     * Loads a template file from the given theme
     * @param    string   $template   The file path of the template to load (without '.tpl.php')
     * @param    string   $module     The name of the module where the template file is loaded from. No module means that
     *                                  the template is loaded from the global templates directory of the theme.
     * @param    string   $theme      The name of the theme where the template file is loaded from (optional)
     * @return   string
     * @access   public
     * @static
     */
    public static function locate($template, $module = null, $theme = null) {
        if (!isset($theme))
            $theme = System::setting('View:Theme');
        
        if (isset($module)) {
            $filename = WW_SITE_PATH.'/modules/'.$module.'/themes/'.$theme.'/templates/'.$template.'.tpl.php';
        } else {
            $filename = WW_ENGINE_PATH.'/themes/'.$theme.'/templates/'.$template.'.tpl.php';
        }
        
        if (!file_exists($filename))
            trigger_error('Template file "'.$filename.'" does not exist', E_USER_ERROR);
        
        return $filename;
    }

}
