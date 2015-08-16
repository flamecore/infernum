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
 * Class for multiselect fields
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class MultiSelectField extends SelectField
{
    public function getTemplateName()
    {
        return '@global/ui/form_field_multi'.$this->style;
    }

    public function setStyle($style)
    {
        $style = (string) $style;

        if ($style != 'select' && $style != 'checkbox') {
            throw new \DomainException(sprintf('Style "%s" is not available for multiselect fields. (expecting one of: select, checkbox)', $style));
        }

        $this->style = $style;

        return $this;
    }

    public function normalize($value)
    {
        return (array) $value;
    }
}
