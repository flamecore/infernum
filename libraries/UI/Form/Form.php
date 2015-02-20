<?php
/**
 * Infernum
 * Copyright (C) 2011 IceFlame.net
 * Permission to use, copy, modify, and/or distribute this software for
 * any purpose with or without fee is hereby granted, provided that the
 * above copyright notice and this permission notice appear in all copies.
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

namespace FlameCore\Infernum\UI\Form;

use FlameCore\Infernum\Application;
use FlameCore\Infernum\Template;
use FlameCore\Infernum\UI\Form\Field\FieldInterface;
use FlameCore\Infernum\UI\Form\Button\ButtonInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for generating and validating forms
 *
 * @author  Christian Neff <christian.neff@gmail.com>
 */
class Form implements \IteratorAggregate, \Countable
{
    private $name;

    private $method;

    private $action;

    private $submitted = false;

    private $data = array();

    private $invalid = array();

    private $fields = array();

    private $buttons = array();

    private $context;

    private static $types = array(
        'hidden' => 'HiddenField',
        'text' => 'TextField',
        'password' => 'PasswordField',
        'textarea' => 'TextAreaField',
        'bool' => 'BooleanField',
        'select' => 'SelectField',
        'multi' => 'MultiSelectField',
        'number' => 'NumberField',
        'date' => 'DateField'
    );

    private static $buttonTypes = array(
        'button' => 'SimpleButton',
        'submit' => 'SubmitButton',
        'reset' => 'ResetButton'
    );

    /**
     * Creates a Form object.
     *
     * @param \FlameCore\Infernum\Application $context The application context
     * @param string $name The name of the form
     * @param string $method The method attribute
     * @param string $action The action attribute (optional)
     */
    public function __construct(Application $context, $name = 'form', $method = 'POST', $action = null)
    {
        $this->context = $context;

        $this->setName($name);
        $this->setMethod($method);
        $this->setAction($action);

        $this->add('hidden', '_submit', [
            'value' => $this->name
        ]);
    }

    /**
     * Gets the name of the form.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of the form.
     *
     * @param string $name The name of the form
     * @return \FlameCore\Infernum\UI\Form\Form
     */
    public function setName($name)
    {
        $this->name = (string) $name;

        return $this;
    }

    /**
     * Gets the method attribute.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets the method attribute.
     *
     * @param string $method The method attribute
     * @return \FlameCore\Infernum\UI\Form\Form
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * Gets the action attribute.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Sets the action attribute.
     *
     * @param string $action The action attribute. Leave empty to use current page path.
     * @return \FlameCore\Infernum\UI\Form\Form
     */
    public function setAction($action)
    {
        $this->action = $action !== null ? $action : $this->context->makePageUrl($this->context->getPagePath());

        return $this;
    }

    /**
     * Gets the application context.
     *
     * @return \FlameCore\Infernum\Application
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Adds a field to the stack.
     *
     * @param string $type The type of the field
     * @param string $name The name of the field
     * @param array $params The parameters of the field
     * @return \FlameCore\Infernum\UI\Form\Form
     */
    public function add($type, $name, array $params = [])
    {
        $name = (string) $name;

        if ($name === '') {
            throw new \InvalidArgumentException('Cannot add field without name.');
        }

        if ($this->has($name)) {
            throw new \LogicException(sprintf('Cannot add field with name "%s" since a field with this name is already defined.', $name));
        }

        if (!isset(self::$types[$type])) {
            throw new \DomainException(sprintf('The form field type "%s" is not valid.', $type));
        }

        $class = sprintf('%s\Field\%s', __NAMESPACE__, self::$types[$type]);
        $this->fields[$name] = new $class($this, $name, $params);

        return $this;
    }

    /**
     * Adds a field object to the stack.
     *
     * @param string $name The name of the field
     * @param \FlameCore\Infernum\UI\Form\Field\FieldInterface $object The field object
     * @return \FlameCore\Infernum\UI\Form\Form
     */
    public function addObject(FieldInterface $object)
    {
        $this->fields[$object->getName()] = $object;

        return $this;
    }

    /**
     * Removes the given field from the stack.
     *
     * @param string $name The name of the field
     * @return \FlameCore\Infernum\UI\Form\Form
     */
    public function remove($name)
    {
        $name = (string) $name;

        if ($name === '') {
            throw new \InvalidArgumentException('Cannot remove field without name.');
        }

        if (!$this->has($name)) {
            throw new \LogicException(sprintf('Cannot remove field with name "%s" since a field with this name is not defined.', $name));
        }

        unset($this->fields[$name]);

        return $this;
    }

    /**
     * Gets the field with given name.
     *
     * @param string $name The name of the field
     * @return \FlameCore\Infernum\UI\Form\Field\FieldInterface
     */
    public function get($name)
    {
        $name = (string) $name;

        return isset($this->fields[$name]) ? $this->fields[$name] : null;
    }

    /**
     * Checks if an input field with the given name is defined.
     *
     * @param string $name The name of the field
     * @return bool
     */
    public function has($name)
    {
        $name = (string) $name;

        return isset($this->fields[$name]);
    }

    /**
     * Adds a button to the form.
     *
     * @param string $type The type of the button
     * @param string $title The title of the button
     * @param array $params The parameters of the field
     * @return \FlameCore\Infernum\UI\Form\Form
     */
    public function addButton($type, $title, array $params = [])
    {
        $title = (string) $title;

        if ($title === '') {
            throw new \InvalidArgumentException('Cannot add button without title.');
        }

        if (!isset(self::$buttonTypes[$type])) {
            throw new \DomainException(sprintf('The form field type "%s" is not valid.', $type));
        }

        $class = sprintf('%s\Button\%s', __NAMESPACE__, self::$buttonTypes[$type]);
        $this->buttons[] = new $class($this, $title, $params);

        return $this;
    }

    /**
     * Adds a button object to the form.
     *
     * @param \FlameCore\Infernum\UI\Form\Button\ButtonInterface $object The button object
     * @return \FlameCore\Infernum\UI\Form\Form
     */
    public function addButtonObject(ButtonInterface $object)
    {
        $this->buttons[] = $object;

        return $this;
    }

    /**
     * Returns the buttons.
     *
     * @return array
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * Returns whether the form was submitted.
     *
     * @return bool
     */
    public function isSubmitted()
    {
        return $this->submitted;
    }

    /**
     * Returns the values of all fields after submission.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns whether the submitted values of all fields are valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->submitted && empty($this->invalid);
    }

    /**
     * Lists fields with invalid values.
     *
     * @return array
     */
    public function getInvalidFields()
    {
        return $this->invalid;
    }

    /**
     * Handles the request.
     *
     * @return void
     */
    public function handleRequest(Request $request)
    {
        if ($this->get('_submit')->retrieve($request) == $this->name) {
            $this->submitted = true;
        }

        if ($this->submitted) {
            foreach ($this->fields as $field) {
                $name = $field->getName();
                $value = $field->retrieve($request);

                if (!$field->validate($value)) {
                    $this->invalid[] = $name;
                }

                $this->data[$name] = $value;
            }
        }
    }

    /**
     * Builds the HTML source of the form from all registered input fields.
     *
     * @return string
     */
    public function render()
    {
        $tpl = new Template('@global/ui/form', $this->context);
        $tpl->set('form', $this);

        if ($this->submitted) {
            $tpl->set('submitted', true);
            $tpl->set('data', $this->data);
            $tpl->set('invalid', $this->invalid);
        }

        return $tpl->render();
    }

    /**
     * Returns an iterator for form fields.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->fields);
    }

    /**
     * Returns the number of form fields on the stack.
     *
     * @return int The number of form fields
     */
    public function count()
    {
        return count($this->fields);
    }
}
