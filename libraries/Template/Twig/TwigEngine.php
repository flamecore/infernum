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

namespace FlameCore\Infernum\Template\Twig;

use FlameCore\Infernum\Application;
use FlameCore\Infernum\Template\TemplateLoader;
use FlameCore\Infernum\Template\EngineInterface;
use Twig_Environment;
use Twig_Extensions_Extension_Text;

/**
 * Twig template engine
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class TwigEngine implements EngineInterface
{
    /**
     * Twig Environment instance
     *
     * @var Twig_Environment
     */
    private $twig;

    /**
     * Generates a TwigEngine object.
     *
     * @param \FlameCore\Infernum\Template\TemplateLoader $loader The template loader
     * @param \FlameCore\Infernum\Application $app The application context
     */
    public function __construct(TemplateLoader $loader, Application $app)
    {
        $engineOptions = array(
            'cache' => $app->isCacheEnabled() ? $app->getCachePath('templates') : false,
            'debug' => $app->isDebugModeEnabled()
        );

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

        $extension = new TwigCoreExtension($app);
        $twig->addExtension($extension);

        $this->twig = $twig;
    }

    /**
     * Renders the given template
     *
     * @param string $name The name of the template
     * @param array $variables The variables to use
     * @return string
     * @throws \FlameCore\Infernum\Template\Exception\NotFoundError
     * @throws \FlameCore\Infernum\Template\Exception\BadNameError
     */
    public function render($name, array $variables = [])
    {
        $template = $this->twig->loadTemplate($name);

        return $template->render($variables);
    }
}
