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
     * The title of the page
     *
     * @var string
     */
    private static $title;

    /**
     * The list of all meta tags
     *
     * @var array
     */
    private static $metatags = array();

    /**
     * The list of all link tags
     *
     * @var array
     */
    private static $linktags = array();

    /**
     * The list of all stylesheets
     *
     * @var array
     */
    private static $stylesheets = array();

    /**
     * The list of all javascripts
     *
     * @var array
     */
    private static $javascripts = array();

    /**
     * Generates a View object
     *
     * @param string $template Name of the template to load
     * @param \FlameCore\Infernum\Application $app The application context
     */
    public function __construct($template, Application $app)
    {
        $template = new Template($template, $app);
        $theme = $app->getTheme();

        $template->set('SITE_URL', $app->getUrl());
        $template->set('SITE_TITLE', $app->setting('site.title'));
        $template->set('PAGE_TITLE', self::$title);

        $template->set('METATAGS', self::$metatags);
        $template->set('LINKTAGS', self::$linktags);

        $stylesheets = array();
        foreach ($theme->getStylesheets() as $stylesheet) {
            $stylesheets[] = array(
                'url' => $app->makeFileUrl($stylesheet['file']),
                'media' => $stylesheet['media']
            );
        }
        $template->set('STYLESHEETS', array_merge($stylesheets, self::$stylesheets));

        $javascripts = array();
        foreach ($theme->getJavascripts() as $javascript) {
            $javascripts[] = array(
                'url' => $app->makeFileUrl($javascript['file']),
                'type' => 'application/javascript'
            );
        }
        $template->set('JAVASCRIPTS', array_merge($javascripts, self::$javascripts));

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
    public static function getTitle()
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
        self::$metatags[] = array(
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
        self::$linktags[] = array(
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
        self::$stylesheets[] = array(
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
        self::$javascripts[] = array(
            'url'  => $url,
            'type' => $type
        );
    }
}
