<?php
/**
 * Infernum
 * Copyright (C) 2011 IceFlame.net
 *
 * Permission to use, copy, modify, and/or distribute this software for
 * any purpose with or without fee is hereby granted, provided that the
 * above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE
 * FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY
 * DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER
 * IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING
 * OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 *
 * @package  FlameCore\Infernum
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

namespace FlameCore\Infernum\UI\Form\Field;

/**
 * Class for number fields
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class NumberField extends SimpleField
{
    public $minimum;

    public $maximum;

    public function initialize($params)
    {
        parent::initialize($params);

        $this->setMaximum(isset($params['max']) ? $params['max'] : null);
        $this->setMinimum(isset($params['min']) ? $params['min'] : null);
    }

    public function getTemplateName()
    {
        return '@global/ui/form_field_number';
    }

    public function setValue($value)
    {
        if ($value === null) {
            $this->value = null;
        } else {
            $this->value = $this->normalize($value);
        }

        return $this;
    }

    public function getMinimum()
    {
        return $this->minimum;
    }

    public function setMinimum($minimum)
    {
        if ($minimum === null) {
            $this->minimum = null;
        } else {
            if ($this->maximum !== null && $minimum > $this->maximum) {
                throw new \InvalidArgumentException(sprintf('The minimum value (%d) must be lower than or equal to the maximum value (%d).', $minimum, $this->maximum));
            }

            $this->minimum = $this->normalize($minimum);
        }

        return $this;
    }

    public function getMaximum()
    {
        return $this->maximum;
    }

    public function setMaximum($maximum)
    {
        if ($maximum === null) {
            $this->maximum = null;
        } else {
            $this->maximum = $this->normalize($maximum);
        }

        return $this;
    }

    public function normalize($value)
    {
        return (int) $value;
    }

    public function validate($value)
    {
        if ($this->minimum !== null && $value < $this->minimum) {
            return false;
        }

        if ($this->maximum !== null && $value > $this->maximum) {
            return false;
        }

        return true;
    }
}
