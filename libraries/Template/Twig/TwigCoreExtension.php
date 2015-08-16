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
use FlameCore\Infernum\Template;
use FlameCore\Infernum\UI\Form\Form;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

/**
 * Core Extension for the Twig template engine
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class TwigCoreExtension extends Twig_Extension
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getGlobals()
    {
        $globals = array();

        if (isset($this->app['session'])) {
            $globals['session'] = $this->app['session'];
            $globals['user'] = $this->app['session']->getUser();
        }

        return $globals;
    }

    public function getFilters()
    {
        $filters = array();

        if (isset($this->app['intl'])) {
            $filters[] = new Twig_SimpleFilter('lformat_number', [$this->app['intl'], 'formatNumber']);
            $filters[] = new Twig_SimpleFilter('lformat_money', [$this->app['intl'], 'formatMoney']);
            $filters[] = new Twig_SimpleFilter('lformat_time', [$this->app['intl'], 'formatTime']);
            $filters[] = new Twig_SimpleFilter('lformat_date', [$this->app['intl'], 'formatDate']);
            $filters[] = new Twig_SimpleFilter('t', [$this->app['intl'], 'translate'], ['is_safe' => ['html']]);
        }

        return $filters;
    }

    public function getFunctions()
    {
        $functions = array();
        $functions[] = new Twig_SimpleFunction('u', [$this->app, 'makeUrl']);
        $functions[] = new Twig_SimpleFunction('page', [$this->app, 'makePageUrl']);
        $functions[] = new Twig_SimpleFunction('file', [$this->app, 'makeFileUrl']);
        $functions[] = new Twig_SimpleFunction('form', [$this, 'renderForm'], ['is_safe' => ['html']]);
        $functions[] = new Twig_SimpleFunction('inject', [$this, 'renderInjection'], ['is_safe' => ['html']]);

        return $functions;
    }

    public function getName()
    {
        return 'infernum_core';
    }

    public function renderForm(Form $form = null)
    {
        if ($form == null) {
            throw new \InvalidArgumentException('Cannot render form without Form object.');
        }

        return $form->render();
    }

    public function renderInjection(Template $template)
    {
        return $template->render();
    }
}
