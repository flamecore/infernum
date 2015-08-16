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
 * Class for textarea fields
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class TextAreaField extends SimpleField
{
    protected $rows;

    protected $cols;

    public function initialize($params)
    {
        parent::initialize($params);

        $this->setRows(isset($params['rows']) ? $params['rows'] : 0);
        $this->setCols(isset($params['cols']) ? $params['cols'] : 0);
    }

    public function getTemplateName()
    {
        return '@global/ui/form_field_textarea';
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function setRows($rows)
    {
        $this->rows = (int) $rows;

        return $this;
    }

    public function getCols()
    {
        return $this->cols;
    }

    public function setCols($cols)
    {
        $this->cols = (int) $cols;

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
