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
 * Class for date fields
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class DateField extends SimpleField
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
        return '@global/ui/form_field_date';
    }

    public function setValue($value)
    {
        if ($value === null) {
            $this->value = null;
        } elseif ($value = $this->normalize($value)) {
            $this->value = $value;
        } else {
            throw new \InvalidArgumentException('The date field value must be either a date string (YYYY-MM-DD) or a DateTime instance or NULL.');
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
        } elseif ($minimum = $this->normalize($minimum)) {
            if ($this->maximum !== null && $minimum > $this->maximum)
                throw new \InvalidArgumentException(sprintf('The minimum date (%s) must be lower than or equal to the maximum date (%s).', $minimum->format('Y-m-d'), $this->maximum->format('Y-m-d')));

            $this->minimum = $minimum;
        } else {
            throw new \InvalidArgumentException('The given minimum date value must be either a date string (YYYY-MM-DD) or a DateTime instance or NULL.');
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
        } elseif ($maximum = $this->normalize($maximum)) {
            $this->maximum = $maximum;
        } else {
            throw new \InvalidArgumentException('The given maximum date value must be either a date string (YYYY-MM-DD) or a DateTime instance or NULL.');
        }

        return $this;
    }

    public function normalize($value)
    {
        if ($value instanceof \DateTime) {
            return $value;
        } elseif (is_string($value) && preg_match('/\d{4}-\d{2}-\d{2}/', $value)) {
            try {
                return new \DateTime($value);
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    public function validate($value)
    {
        if (!$value instanceof \DateTime)
            return false;

        if ($this->minimum !== null && $value < $this->minimum)
            return false;

        if ($this->maximum !== null && $value > $this->maximum)
            return false;

        return true;
    }
}
