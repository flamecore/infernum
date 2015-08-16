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

namespace FlameCore\Infernum\UI\Form\Button;

/**
 * Base class for simple buttons
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class SimpleButton extends AbstractButton
{
    protected $class;

    public function initialize($params)
    {
        $this->setClass(isset($params['class']) ? $params['class'] : false);
    }

    public function getType()
    {
        return 'button';
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $class = (string) $class;

        $this->class = $class !== '' ? $class : false;

        return $this;
    }
}
