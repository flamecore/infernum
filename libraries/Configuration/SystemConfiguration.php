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
 * The SystemConfiguration class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class SystemConfiguration extends AbstractConfiguration
{
    /**
     * {@inheritdoc}
     */
    protected function getDefinitionTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('config');

        $rootNode
            ->children()
                ->booleanNode('enable_debugmode')->defaultValue(true)->end()
                ->booleanNode('enable_logging')->defaultValue(true)->end()
                ->booleanNode('enable_caching')->defaultValue(true)->end()
                ->integerNode('cache_lifetime')->defaultValue(86400)->end()
                ->booleanNode('enable_multisite')->defaultValue(true)->end()
                ->arrayNode('sites')->end()
            ->end();

        return $treeBuilder;
    }
}
