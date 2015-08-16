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

namespace FlameCore\Infernum\UI\Form\Field;

/**
 * Class for password fields
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class PasswordField extends SimpleField
{
    protected $size;

    public function initialize($params)
    {
        parent::initialize($params);

        $this->setSize(isset($params['size']) ? $params['size'] : false);
    }

    public function getTemplateName()
    {
        return '@global/ui/form_field_password';
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    public function getMaxLength()
    {
        return isset($this->asserts['max_length']) ? $this->asserts['max_length'] : false;
    }

    public function setMaxLength($maxLength)
    {
        $this->asserts['max_length'] = $maxLength;

        return $this;
    }

    public function normalize($value)
    {
        return (string) $value;
    }
}
