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
 * The SiteSettings class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class SiteSettings extends AbstractConfiguration
{
    /**
     * {@inheritdoc}
     */
    protected function getDefinitionTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('settings');

        $rootNode
            ->children()
                ->arrayNode('site')
                    ->children()
                        ->scalarNode('title')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('frontpage')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('language')->defaultValue('en-US')->end()
                        ->scalarNode('timezone')->defaultValue(date_default_timezone_get())->end()
                    ->end()
                ->end()
                ->arrayNode('web')
                    ->children()
                        ->scalarNode('path')
                            ->defaultValue('/')
                            ->cannotBeEmpty()
                        ->end()
                        ->booleanNode('url_rewrite')->defaultValue(false)->end()
                        ->scalarNode('theme')
                            ->defaultValue('flamecore/default')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('database')
                    ->isRequired()
                    ->children()
                        ->scalarNode('driver')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('host')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('user')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('password')->defaultValue('')->end()
                        ->scalarNode('database')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('prefix')->defaultValue('')->end()
                        ->scalarNode('charset')->defaultValue('utf8')->end()
                    ->end()
                ->end()
                ->arrayNode('session')
                    ->children()
                        ->integerNode('lifetime')->defaultValue(3600)->end()
                        ->integerNode('online_threshold')->defaultValue(600)->end()
                    ->end()
                ->end()
                ->arrayNode('guest')
                    ->children()
                        ->scalarNode('username')->defaultValue('')->end()
                        ->integerNode('group')->defaultValue(1)->end()
                    ->end()
                ->end()
                ->arrayNode('cookie')
                    ->children()
                        ->scalarNode('name_prefix')->defaultValue('')->end()
                        ->scalarNode('domain')->defaultValue('')->end()
                        ->scalarNode('path')->defaultValue('/')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
