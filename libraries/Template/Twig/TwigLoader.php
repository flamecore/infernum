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

use FlameCore\Infernum\Template\TemplateLocator;
use FlameCore\Infernum\Template\Exception\NotFoundError;
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
        } catch (NotFoundError $e) {
            return false;
        }
    }
}
