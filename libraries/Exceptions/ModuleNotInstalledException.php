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
 * This exception is thrown if a module is not installed.
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class ModuleNotInstalledException extends SystemException
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * Creates the exception.
     *
     * @param string $moduleName The name of the missing module
     */
    public function __constructor($moduleName)
    {
        $this->moduleName = $moduleName;

        parent::__construct(sprintf('Could not load module "%s" because it is not installed.', $moduleName));
    }

    /**
     * Returns the name of the missing module.
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }
}
