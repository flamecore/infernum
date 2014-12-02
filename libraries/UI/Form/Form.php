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

namespace FlameCore\Infernum\UI\Form;

use FlameCore\Infernum\Application;
use FlameCore\Infernum\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for generating and validating forms
 *
 * @author  Christian Neff <christian.neff@gmail.com>
 */
class Form
{
    private $name;

    private $method;

    private $action;

    private $submitted = false;

    private $data = array();

    private $invalid = array();

    private $stack = array();

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
        $this->action = $action !== null ? $action : $this->context->makePageURL($this->context->getPagePath());

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
    public function add($type, $name, $params = array())
    {
        if (empty($name))
            throw new \InvalidArgumentException('Cannot add field without name.');

        if ($this->has($name))
            throw new \LogicException(sprintf('Cannot add field with name "%s" since a field with this name is already defined.', $name));

        if (!isset(self::$types[$type]))
            throw new \DomainException(sprintf('The form field type "%s" is not valid.', $type));

        $class = sprintf('%s\Field\%s', __NAMESPACE__, self::$types[$type]);
        $this->stack[$name] = new $class($this, $name, $params);

        return $this;
    }

    /**
     * Removes the given field from the stack.
     *
     * @param string $name The name of the field
     * @return \FlameCore\Infernum\UI\Form\Form
     */
    public function remove($type, $name, $params = array())
    {
        if (empty($name))
            throw new \InvalidArgumentException('Cannot remove field without name.');

        if ($this->has($name))
            throw new \LogicException(sprintf('Cannot remove field with name "%s" since a field with this name is not defined.', $name));

        unset($this->stack[$name]);

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
        return isset($this->stack[$name]) ? $this->stack[$name] : null;
    }

    /**
     * Checks if an input field with the given name is defined.
     *
     * @param string $name The name of the field
     * @return bool
     */
    public function has($name)
    {
        return isset($this->stack[$name]);
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
        if ($this->get('_submit')->retrieve($request) == $this->name)
            $this->submitted = true;

        if ($this->submitted) {
            foreach ($this->stack as $field) {
                $name = $field->getName();
                $value = $field->retrieve($request);

                if (!$field->validate($value))
                    $this->invalid[] = $name;

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
        $tpl->set('name', $this->name);
        $tpl->set('action', $this->action);
        $tpl->set('method', $this->method);
        $tpl->set('stack', $this->stack);

        if ($this->submitted) {
            $tpl->set('submitted', true);
            $tpl->set('data', $this->data);
            $tpl->set('invalid', $this->invalid);
        }

        return $tpl->render();
    }
}
