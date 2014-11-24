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
 * Class for text fields
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class TextField extends SimpleField
{
    protected $style;

    protected $size;

    public function initialize($params)
    {
        parent::initialize($params);

        $this->setStyle(isset($params['style']) ? $params['style'] : 'text');
        $this->setSize(isset($params['size']) ? $params['size'] : false);
    }

    public function getTemplateName()
    {
        return '@global/ui/form_field_text';
    }

    public function getStyle()
    {
        return $this->style;
    }

    public function setStyle($style)
    {
        $style = (string) $style;

        if ($style != 'text' && $style != 'password')
            throw new \DomainException(sprintf('Style "%s" is not available for text fields. (expecting one of: text, password)', $style));

        $this->style = $style;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getMaxLength()
    {
        return isset($this->asserts['max_length']) ? $this->asserts['max_length'] : false;
    }

    public function setMaxLength($maxLength)
    {
        $this->asserts['max_length'] = $maxLength;
    }
}
