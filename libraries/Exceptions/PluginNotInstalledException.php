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

namespace FlameCore\Infernum\Exceptions;

/**
 * This exception is thrown if a plugin is not installed.
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class PluginNotInstalledException extends SystemException
{
    /**
     * @var string
     */
    private $pluginName;

    /**
     * Creates the exception.
     *
     * @param string $pluginName The name of the missing plugin
     */
    public function __constructor($pluginName)
    {
        $this->pluginName = $pluginName;

        parent::__construct(sprintf('Could not load plugin "%s" because it is not installed.', $pluginName));
    }

    /**
     * Returns the name of the missing plugin.
     *
     * @return string
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }
}
