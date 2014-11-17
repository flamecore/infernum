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

namespace FlameCore\Infernum\Configuration;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * The abstract Configuration class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class AbstractConfiguration
{
    private $files = array();

    private $treeBuilder;

    private $processor;

    public function __construct($files)
    {
        $this->files = (array) $files;

        $treeBuilder = $this->getDefinitionTree();

        if (!$treeBuilder instanceof TreeBuilder)
            throw new \UnexpectedValueException(sprintf('%s::getDefinitionTree() does not provide a Symfony\Component\Config\Definition\Builder\TreeBuilder object.', get_class($this)));

        $this->treeBuilder = $treeBuilder;
        $this->processor = new Processor();
    }

    public function load()
    {
        $configs = array();
        foreach ($this->files as $file) {
            if (!file_exists($file))
                throw new \LogicException(sprintf('File "%s" does not exist.', $file));
            $configs[] = Yaml::parse($file);
        }

        return $this->processor->process($this->treeBuilder->buildTree(), $configs);
    }

    abstract protected function getDefinitionTree();
}
