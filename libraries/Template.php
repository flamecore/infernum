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

use FlameCore\Infernum\Template\Loader;
use FlameCore\Infernum\Template\CoreExtension;
use Twig_Environment;

/**
 * Template object
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 * @author   Martin Lantzsch <martin@linux-doku.de>
 */
class Template
{
    /**
     * Name of the template to load (without file extension)
     *
     * @var string
     */
    private $name;

    /**
     * Twig Environment instance
     *
     * @var Twig_Environment
     */
    private $twig;

    /**
     * All assigned template variables
     *
     * @var array
     */
    private $variables = array();

    /**
     * All assigned global template variables
     *
     * @var array
     */
    private static $globals = array();

    /**
     * Generates a new Template object.
     *
     * @param    string   $source    Module or template @namespace where the template is loaded from
     * @param    string   $name      Name of the template to load (without file extension)
     * @param    array    $options   An optional array of one or more of the following options:
     *                                 * theme: Name of the theme where the template is loaded from
     */
    public function __construct($source, $name, array $options = [])
    {
        $theme = $options['theme'] ?: System::setting('View:Theme', 'default');

        $engine_options = array(
            'cache' => false,
            'debug' => false
        );

        if (config('enable_caching')) {
            $cache_path = WW_CACHE_PATH.'/templates/'.strtolower($type);

            if (!is_dir($cache_path))
                mkdir($cache_path, 0777, true);

            $engine_options['cache'] = $cache_path;
        }

        if (config('enable_debugmode'))
            $engine_options['debug'] = true;

        $loader = new Loader();
        $loader->setNamespace('global', INFERNUM_ENGINE_PATH.'/themes/'.$theme.'/templates');

        if ($source[0] != '@') {
            $loader->setLocalPath(INFERNUM_ENGINE_PATH.'/modules/'.$source.'/themes/'.$theme.'/templates');
            $this->name = $name;
        } else {
            $this->name = $source.'/'.$name;
        }

        $twig = new Twig_Environment($loader, $engine_options);
        $twig->addExtension(new CoreExtension);

        $this->twig = $twig;
    }

    /**
     * Returns the rendered template.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return '';
        }
    }

    /**
     * Renders the template
     *
     * @return string
     */
    public function render()
    {
        $variables = array_merge(self::$globals, $this->variables);

        $object = $this->twig->loadTemplate($this->name);
        return $object->render($variables);
    }

    /**
     * Displays the template
     *
     * @return void
     */
    public function display()
    {
        echo $this->render();
    }

    /**
     * Sets one or more template variables
     *
     * @param mixed $param1 The name of the variable (string) or pairs of names and values of multiple
     *   variables (array in the format `[name => value, ...]`) to be set
     * @param mixed $param2 The value of the variable (only if parameter 1 is used for the variable name)
     */
    public function set($param1, $param2 = null)
    {
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
     *
     * @param mixed $param1 The name of the variable (string) or pairs of names and values of multiple
     *   variables (array in the format `[name => value, ...]`) to be set
     * @param mixed $param2 The value of the variable (only if parameter 1 is used for the variable name)
     */
    public static function setGlobal($param1, $param2 = null)
    {
        if (is_array($param1)) {
            // Set multiple variables
            array_merge(self::$globals, $param1);
        } elseif (is_string($param1) && isset($param2)) {
            // Set a single variable
            self::$globals[$param1] = $param2;
        }
    }
}
