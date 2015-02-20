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
