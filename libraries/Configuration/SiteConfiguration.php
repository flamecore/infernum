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

namespace FlameCore\Infernum\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * The SiteConfiguration class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class SiteConfiguration extends AbstractConfiguration
{
    /**
     * {@inheritdoc}
     */
    protected function getDefinitionTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('siteconfig');

        $rootNode
            ->children()
                ->arrayNode('routes')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('module')->isRequired()->end()
                            ->scalarNode('alias')->end()
                            ->variableNode('extra')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('plugins')
                    ->defaultValue([])
                    ->prototype('scalar')
                    ->beforeNormalization()
                        ->ifArray()
                        ->then(function ($v) { return array_unique($v); })
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
