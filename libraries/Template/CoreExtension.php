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
 * @package  FlameCore\Webwork
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

namespace FlameCore\Webwork\Template;

use Twig_Extension, Twig_SimpleFilter, Twig_SimpleFunction;

/**
 * Core Extension for the Twig template engine
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class CoreExtension extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('shorten', 'FlameCore\Webwork\Format::shorten'),
            new Twig_SimpleFilter('lformat_number', 'FlameCore\Webwork\Format::number'),
            new Twig_SimpleFilter('lformat_money', 'FlameCore\Webwork\Format::money'),
            new Twig_SimpleFilter('lformat_time', 'FlameCore\Webwork\Format::time'),
            new Twig_SimpleFilter('lformat_date', 'FlameCore\Webwork\Format::date')
        );
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('u', 'FlameCore\Webwork\Util::makeURL'),
            new Twig_SimpleFunction('page', 'FlameCore\Webwork\Util::makePageURL'),
            new Twig_SimpleFunction('theme', 'FlameCore\Webwork\Util::makeThemeFileURL'),
            new Twig_SimpleFunction('t', 'FlameCore\Webwork\International::translate'),
            new Twig_SimpleFunction('page_title', 'FlameCore\Webwork\View::getTitle'),
            new Twig_SimpleFunction('head_tags', 'FlameCore\Webwork\View::getHeadTags')
        );
    }

    public function getName()
    {
        return 'webwork_core';
    }
}
