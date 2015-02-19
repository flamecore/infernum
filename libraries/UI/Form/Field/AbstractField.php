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

use FlameCore\Infernum\UI\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base class for form fields.
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class AbstractField implements FieldInterface
{
    /**
     * The form object
     *
     * @var \FlameCore\Infernum\UI\Form\Form
     */
    protected $form;

    /**
     * The name of the form field
     *
     * @var string
     */
    protected $name;

    /**
     * The value of the form field
     *
     * @var mixed
     */
    protected $value;

    /**
     * The title of the form field
     *
     * @var string
     */
    protected $title;

    /**
     * The description of the form field
     *
     * @var string
     */
    protected $description;

    /**
     * The error text of the form field
     *
     * @var string
     */
    protected $errorText;

    /**
     * The assertations of the form field
     *
     * @var array
     */
    protected $asserts;

    /**
     * {@inheritdoc}
     */
    public function __construct(Form $form, $name, array $params = [])
    {
        $this->form = $form;
        $this->setName($name);

        $this->setValue(isset($params['value']) ? $params['value'] : null);
        $this->setTitle(isset($params['title']) ? $params['title'] : false);
        $this->setDescription(isset($params['description']) ? $params['description'] : false);
        $this->setErrorText(isset($params['error_text']) ? $params['error_text'] : false);
        $this->setAsserts($params);

        $this->initialize($params);
    }

    /**
     * Initializes the form field.
     *
     * @param array $params The form field parameters
     */
    public function initialize(array $params)
    {
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getTemplateName();

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $name = (string) $name;

        if (empty($name)) {
            throw new \InvalidArgumentException('The form field name must not be empty.');
        }

        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->value = $this->normalize($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $title = (string) $title;

        $this->title = $title !== '' ? $title : false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $description = (string) $description;

        $this->description = $description !== '' ? $description : false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorText()
    {
        return $this->errorText;
    }

    /**
     * {@inheritdoc}
     */
    public function setErrorText($errorText)
    {
        $errorText = (string) $errorText;

        $this->errorText = $errorText !== '' ? $errorText : false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAsserts()
    {
        return $this->asserts;
    }

    /**
     * {@inheritdoc}
     */
    public function setAsserts(array $asserts)
    {
        $this->asserts = array(
            'required'   => isset($asserts['required']) ? (bool) $asserts['required'] : false,
            'min_length' => isset($asserts['min_length']) ? (int) $asserts['min_length'] : null,
            'max_length' => isset($asserts['max_length']) ? (int) $asserts['max_length'] : null
        );

        return $this;
    }

    /**
     * Returns whether a value is required for this field.
     *
     * @return bool
     */
    public function isRequired()
    {
        if ($this->asserts['required']) {
            return true;
        }

        return isset($this->asserts['min_length']);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieve(Request $request)
    {
        $values = $this->form->getMethod() == 'GET' ? $request->query : $request->request;

        return $this->normalize($values->get($this->name));
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        if ($this->asserts['required'] && (string) $value === '') {
            return false;
        }

        if (isset($this->asserts['min_length']) && strlen($value) < (int) $this->asserts['min_length']) {
            return false;
        }

        if (isset($this->asserts['max_length']) && strlen($value) > (int) $this->asserts['max_length']) {
            return false;
        }

        return true;
    }
}
