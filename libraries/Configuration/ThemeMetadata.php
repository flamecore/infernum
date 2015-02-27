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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * The ThemeMetadata class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class ThemeMetadata extends AbstractConfiguration
{
    /**
     * {@inheritdoc}
     */
    protected function getDefinitionTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('theme');

        $rootNode
            ->children()
                ->scalarNode('title')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('description')->end()
                ->scalarNode('author')->end()
                ->scalarNode('website')->end()
                ->arrayNode('stylesheets')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('file')->isRequired()->end()
                            ->scalarNode('media')->defaultValue('all')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('javascripts')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('file')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
