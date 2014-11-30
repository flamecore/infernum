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
use Twig_Extensions_Extension_Text;

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
     * Generates a Template object.
     *
     * @param string $name Name of the template to load (without file extension)
     * @param \FlameCore\Infernum\Application $app The application context
     * @param array $options An optional array of one or more of the following options:
     *   * cache: An absolute path where to store compiled templates, or FALSE to disable compilation cache (default).
     *   * debug: Enable debugmode for the template engine
     */
    public function __construct($name, Application $app, array $options = [])
    {
        $engineOptions = array(
            'cache' => (isset($options['cache']) ? $options['cache'] : $app->isCacheEnabled()) ? $app->getCachePath('templates') : false,
            'debug' => isset($options['debug']) ? $options['debug'] : $app->isDebugModeEnabled()
        );

        $loader = new Loader();
        $loader->setNamespace('global', $app->getTemplatePath());

        if ($name[0] != '@') {
            $localPath = $app->getTemplatePath(true);

            if (!$localPath)
                throw new \DomainException(sprintf('Cannot use local template path "%s" when no extension is running.', $name));

            $loader->setLocalPath($localPath);
        }

        $twig = new Twig_Environment($loader, $engineOptions);
        $twig->getExtension('core')->setTimezone($app->setting('site.timezone'));

        if (isset($app['intl'])) {
            $locale = $app['intl']->getLocale();

            $separators = $locale->getNumberSeparators();
            $twig->getExtension('core')->setNumberFormat(0, $separators['decimal'], $separators['thousand']);

            $format = $locale->getDateFormat();
            $twig->getExtension('core')->setDateFormat($format, '%d days');
        }

        $twig->addExtension(new Twig_Extensions_Extension_Text);

        $extension = new CoreExtension($app);
        $twig->addExtension($extension);

        $this->twig = $twig;
        $this->name = $name;
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
