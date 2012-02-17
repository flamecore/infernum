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
 * @author  Martin Lantzsch <martin@linux-doku.de>
 */
class Template {

    /**
     * The file path of the template to load (without '.tpl')
     * @var      string
     * @access   private
     */
    private $_filePath;

    /**
     * The name of the theme where the template file is loaded from
     * @var      string
     * @access   private
     */
    private $_theme;

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
    private static $_headTags = array(array(), array(), array(), array());

    /**
     * Generates a new template object
     * @param    string   $file     The name of the template to load (without '.tpl')
     * @param    string   $module   The name of the module where the template file is loaded from
     * @param    string   $theme    The name of the theme where the template file is loaded from. Optional.
     * @return   void
     * @access   public
     */
    public function __construct($file, $module, $theme = null) {
        $this->_filePath = $module.'/'.$file;
        
        if (isset($theme)) {
            $this->_theme = $theme;
        } else {
            $this->_theme = Settings::get('core', 'theme');
        }
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
     * @param    bool     $output   Output generated template? Defaults to TRUE.
     * @return   string
     * @access   public
     */
    public function render($output = true) {
        $cacheName = 'tpl_'.md5($this->_theme.$this->_filePath);
        $cache = new Cache($cacheName, 0, false);
        if ($cache->active) {
            // get template from cache
            $template = $cache->read();
        } else {
            // load the template file
            $templateCode = self::loadFile($this->_filePath, $this->_theme);
            
            // parse the template code
            $template = self::parse($templateCode, $this->_theme);
            
            // store template to cache if enabled
            $cache->store($template);
        }
        
        // create the renderer function
        $render = @create_function('', 'extract(func_get_arg(0)); ob_start(); ?>'.$template.'<?php return ob_get_clean();');
        
        if (!$render)
            throw new Exception('Syntax error in template code');
        
        // render the template
        $variables = self::$_globalVars + $this->_variables;
        $content = $render($variables);
        
        // output/return the template
        if ($output) {
            echo $content;
        } else {
            return $content;
        }
    }
    
    /**
     * Loads a template file from the given theme
     * @param    string   $file    The file path of the template to load (without '.tpl')
     * @param    string   $theme   The name of the theme where the template file is loaded from. Optional.
     * @return   string
     * @access   public
     * @static 
     */
    public static function loadFile($file, $theme = null) {
        if (!isset($theme))
            $theme = Settings::get('core', 'theme');
        
        $templateFile = WW_DIR_THEMES.'/'.$theme.'/templates'.'/'.$file.'.tpl';
        
        if (!file_exists($templateFile))
            throw new Exception('Template file "'.$templateFile.'" does not exist');
        
        return file_get_contents($templateFile);
    }
    
    /**
     * Transforms template code to real PHP code
     * @param    string   $code    The template code to transform
     * @param    string   $theme   The name of the theme where the template file is loaded from. Optional.
     * @return   string
     * @access   public
     * @static 
     */
    public static function parse($code, $theme = null) {
        if (!isset($theme))
            $theme = Settings::get('core', 'theme');
        
        // replace @constants@
        $rootURL = Settings::get('core', 'url');
        $code = str_replace('@URL_ROOT@', $rootURL, $code);
        $code = str_replace('@URL_THEME@', $rootURL.'/themes/'.$theme, $code);
        
        // replace {include] tags
        $replaceInclude = function ($match) use ($theme) {
            $templateCode = Template::loadFile($match[1], $theme);
            return Template::parse($templateCode, $theme);
        };
        $code = preg_replace_callback('#\{include ([\w\./]+)\}#i', $replaceInclude, $code);

        // replace conditional tags
        $code = preg_replace('#\{(if|elseif|while|for|foreach) ([^\}\r\n]+)(?<!\s)\}#i', '<?php $1 ($2): ?>', $code);
        $code = preg_replace('#\{else\}#i', '<?php else: ?>', $code);
        $code = preg_replace('#\{/(if|while|for|foreach)\}#i', '<?php end$1; ?>', $code);

        // replace other tags like variables, constants, function calls, ...
        $code = preg_replace('#\{(?!\s)([^\{\r\n]+)(?<!\s)\}#', '<?php echo $1; ?>', $code);
        
        return $code;
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
        self::$_headTags[0][] = '<meta name="'.$name.'" content="'.$content.'" />';
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
        self::$_headTags[1][] = '<link rel="'.$rel.'" href="'.$url.'" type="'.$type.'" />';
    }

    /**
     * Adds a stylesheet link to the head tags using the theme URL
     * @param    string   $path    The URL to the file
     * @param    string   $media   Only for this media types. Defaults to 'all'.
     * @return   void
     * @access   public
     * @static
     */
    public static function addThemeCSS($path, $media = 'all') {
        $rootURL = Settings::get('core', 'url');
        $theme   = Settings::get('core', 'theme');
        
        // build URL
        $url = $rootURL.'/themes/'.$theme.'/'.$path;
        
        self::$_headTags[2][] = '<link rel="stylesheet" href="'.$url.'" type="text/css" media="'.$media.'" />';
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
        self::$_headTags[2][] = '<link rel="stylesheet" href="'.$url.'" type="text/css" media="'.$media.'" />';
    }

    /**
     * Adds a JavaScript to the head tags
     * @param    string   $url   The URL to the file
     * @return   void
     * @access   public
     * @static
     */
    public static function addScript($url) {
        self::$_headTags[3][] = '<script src="'.$url.'"></script>';
    }

    /**
     * Lists all registered head tags sorted by group
     * @return   array
     * @access   public
     * @static
     */
    public static function getHeadTags() {
        $tagsList = array_merge(self::$_headTags[0], self::$_headTags[1], self::$_headTags[2], self::$_headTags[3]);
        return $tagsList;
    }

}
