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

use FlameCore\Infernum\Template\TemplateLocator;
use FlameCore\Infernum\Template\NotFoundException;
use Twig_LoaderInterface;
use Twig_ExistsLoaderInterface;

/**
 * Loader for the Twig template engine
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class TwigLoader implements Twig_LoaderInterface, Twig_ExistsLoaderInterface
{
    private $locator;

    final public function __construct(TemplateLocator $locator)
    {
        $this->locator = $locator;
    }

    public function getSource($template)
    {
        return file_get_contents($this->locator->locate($template));
    }

    public function getCacheKey($template)
    {
        return $this->locator->locate($template);
    }

    public function isFresh($template, $time)
    {
        return filemtime($this->locator->locate($template)) <= $time;
    }

    public function exists($template)
    {
        try {
            $this->locator->locate($template);
            return true;
        } catch (NotFoundException $e) {
            return false;
        }
    }
}
