<?php
/**
 * Infernum
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
 * @package  FlameCore\Infernum
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

namespace FlameCore\Infernum;

/**
 * View manager
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class View
{
    /**
     * The loaded template object
     *
     * @var object
     */
    private $template;

    /**
     * The name of the theme to use (Default: value given in settings)
     *
     * @var string
     */
    private static $theme;

    /**
     * The title of the page
     *
     * @var string
     */
    private static $title;

    /**
     * The list of all head tags
     *
     * @var array
     */
    private static $headTags = array();

    /**
     * Generates a View object
     *
     * @param string $template Name of the template to load
     * @param \FlameCore\Infernum\Application $app The application context
     */
    public function __construct($template, Application $app)
    {
        $template = new Template($template, $app);

        $template->set('SITE_URL', $app['url']);
        $template->set('SITE_TITLE', $app->setting('site.title'));
        $template->set('PAGE_TITLE', self::getTitle());
        $template->set('METATAGS', self::getHeadTags('meta'));
        $template->set('LINKTAGS', self::getHeadTags('link'));
        $template->set('STYLESHEETS', self::getHeadTags('css'));
        $template->set('JAVASCRIPTS', self::getHeadTags('script'));

        $this->template = $template;
    }

    /**
     * Renders the view
     *
     * @return string
     */
    public function render()
    {
        return $this->template->render();
    }

    /**
     * Displays the view
     *
     * @return void
     */
    public function display()
    {
        $this->template->display();
    }

    /**
     * Sets one or more template variables for this view
     *
     * @param mixed $param1 The name of the variable (string) or pairs of names and values of multiple
     *   variables (array in the format `[name => value, ...]`) to be set
     * @param mixed $param2 The value of the variable (only if parameter 1 is used for the variable name)
     */
    public function set($param1, $param2 = null)
    {
        $this->template->set($param1, $param2);
    }

    /**
     * Returns the page title
     *
     * @return string
     */
    public static function getTitle($title = null, $append = true)
    {
        return self::$title;
    }

    /**
     * Sets the page title
     *
     * @param string $title The text to set as title or append to the title
     * @param bool $append Should the given text be appended to the currently set title? (Default: TRUE)
     */
    public static function setTitle($title, $append = true)
    {
        if ($append && self::$title != '') {
            self::$title = $title.' &bull; '.self::$title;
        } else {
            self::$title = (string) $title;
        }
    }

    /**
     * Adds a meta tag to the head tags
     *
     * @param string $name The name of the meta tag
     * @param string $content The value of the meta tag
     */
    public static function addMetaTag($name, $content)
    {
        self::$headTags['meta'][] = array(
            'name'    => $name,
            'content' => $content
        );
    }

    /**
     * Adds a link tag to the head tags
     *
     * @param string $rel The relation attribute
     * @param string $url The URL to the file
     * @param string $type The type attribute
     */
    public static function addLinkTag($rel, $url, $type)
    {
        self::$headTags['link'][] = array(
            'rel'  => $rel,
            'href' => $url,
            'type' => $type
        );
    }

    /**
     * Adds a stylesheet link to the head tags
     *
     * @param string $url The URL to the file
     * @param string $media Only for this media types (Default: 'all')
     */
    public static function addCSS($url, $media = 'all')
    {
        self::$headTags['css'][] = array(
            'url'   => $url,
            'media' => $media
        );
    }

    /**
     * Adds a JavaScript to the head tags
     *
     * @param string $url The URL to the file
     * @param string $type The content type of the script (Default: 'text/javascript')
     */
    public static function addScript($url, $type = 'application/javascript')
    {
        self::$headTags['script'][] = array(
            'url'  => $url,
            'type' => $type
        );
    }

    /**
     * Lists all registered head tags of a given group
     *
     * @param string $group The group of head tags to return
     * @return array
     */
    public static function getHeadTags($group)
    {
        if (isset(self::$headTags[$group])) {
            return self::$headTags[$group];
        } else {
            return array();
        }
    }
}
