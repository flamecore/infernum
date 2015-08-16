<?php
/**
 * Infernum
 * Copyright (C) 2015 IceFlame.net
 *
 * Permission to use, copy, modify, and/or distribute this software for
 * any purpose with or without fee is hereby granted, provided that the
 * above copyright notice and this permission notice appear in all copies.
 *
 * @package  FlameCore\Infernum
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  http://opensource.org/licenses/ISC ISC License
 */

namespace FlameCore\Infernum\Template\Twig;

use FlameCore\Infernum\Application;
use FlameCore\Infernum\Template\TemplateLocator;
use FlameCore\Infernum\Template\EngineInterface;
use Twig_Environment;
use Twig_Extension_Debug;
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
     * @param \FlameCore\Infernum\Template\TemplateLocator $locator The template locator
     * @param \FlameCore\Infernum\Application $app The application context
     */
    public function __construct(TemplateLocator $locator, Application $app)
    {
        $engineOptions = array(
            'cache' => $app->isCacheEnabled() ? $app->getCachePath('templates') : false,
            'debug' => $app->isDebugModeEnabled()
        );

        $loader = new TwigLoader($locator);

        $twig = new Twig_Environment($loader, $engineOptions);
        $twig->getExtension('core')->setTimezone($app->setting('site.timezone'));

        if (isset($app['intl'])) {
            $locale = $app['intl']->getLocale();

            $separators = $locale->getNumberSeparators();
            $twig->getExtension('core')->setNumberFormat(0, $separators['decimal'], $separators['thousand']);

            $format = $locale->getDateFormat();
            $twig->getExtension('core')->setDateFormat($format, '%d days');
        }

        if ($engineOptions['debug']) {
            $twig->addExtension(new Twig_Extension_Debug);
        }

        $twig->addExtension(new Twig_Extensions_Extension_Text);

        $extension = new TwigCoreExtension($app);
        $twig->addExtension($extension);

        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function render($name, array $variables = [])
    {
        $template = $this->twig->loadTemplate($name);

        return $template->render($variables);
    }
}
