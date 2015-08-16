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
 * The ModuleMetadata class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class ModuleMetadata extends AbstractConfiguration
{
    /**
     * {@inheritdoc}
     */
    protected function getDefinitionTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('module');

        $rootNode
            ->children()
                ->scalarNode('title')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('description')->end()
                ->scalarNode('author')->end()
                ->scalarNode('website')->end()
                ->scalarNode('namespace')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('provides')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('libraries')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('requires')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('plugins')
                            ->defaultValue([])
                            ->prototype('scalar')
                            ->beforeNormalization()
                                ->ifArray()
                                ->then(function ($v) { return array_unique($v); })
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
