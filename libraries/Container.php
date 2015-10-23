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

namespace FlameCore\Infernum;

use FlameCore\Container\LockableContainer;

/**
 * The Container class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Container extends LockableContainer
{
    /**
     * {@inheritdoc}
     *
     * @param string $name The name
     * @param array $typemap The typemap
     *
     * @throws \InvalidArgumentException if the name is invalid.
     */
    public function __construct($name, array $typemap = [])
    {
        $name = (string) $name;

        if ($name === '') {
            throw new \InvalidArgumentException('The container name must not be empty.');
        }

        $this->name = $name;

        $this->defineAll($typemap);
    }
}
