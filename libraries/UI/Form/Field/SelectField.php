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
 * Class for select fields
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class SelectField extends SimpleField
{
    protected $style;

    protected $options;

    protected $size;

    public function initialize($params)
    {
        parent::initialize($params);

        $this->setStyle(isset($params['style']) ? $params['style'] : 'select');
        $this->setOptions(isset($params['options']) ? (array) $params['options'] : []);
        $this->setSize(isset($params['size']) ? $params['size'] : false);
    }

    public function getTemplateName()
    {
        return '@global/ui/form_field_'.$this->style;
    }

    public function getStyle()
    {
        return $this->style;
    }

    public function setStyle($style)
    {
        $style = (string) $style;

        if ($style != 'select' && $style != 'radio') {
            throw new \DomainException(sprintf('Style "%s" is not available for select fields. (expecting one of: select, radio)', $style));
        }

        $this->style = $style;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
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
}
