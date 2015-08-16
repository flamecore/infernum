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

use FlameCore\Infernum\UI\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * The Field interface
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
interface FieldInterface
{
    /**
     * Constructor
     *
     * @param \FlameCore\Infernum\UI\Form\Form $form The form object
     * @param string $name The name of the form field
     * @param array $params A list of one or more of the following options as an array:
     *   * title        The title of the field
     *   * description  The description of the field
     *   * error_text   The text to display if the value is invalid
     *   * required     A value is required for this field
     *   * equal        The value must be equal to `x`
     *   * not_equal    The value must not be equal to `x`
     *   * min_range    The value must be greater than or equal to `n`
     *   * max_range    The value must be lower than or equal to `n`
     *   * min_length   The value's length must be at least `n` characters
     *   * max_length   The value's length must not be greater than `n` characters
     *   * scheme       The value must match this scheme. Possible values are 'email', 'url', 'ip' or 'regex'.
     *   * pattern      The value must match this regular expression. This option is only availabe if 'scheme' is set to 'regex'.
     */
    public function __construct(Form $form, $name, array $params);

    /**
     * Gets the template name of this field
     *
     * @return string
     */
    public function getTemplateName();

    /**
     * Gets the form object.
     *
     * @return \FlameCore\Infernum\UI\Form\Form
     */
    public function getForm();

    /**
     * Gets the name of the field.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name of the field.
     *
     * @param string $name The name of the field
     * @return \FlameCore\Infernum\UI\Form\Field\FieldInterface
     */
    public function setName($name);

    /**
     * Gets the value of the field.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Sets the value of the field.
     *
     * @param mixed $value The value of the field
     * @return \FlameCore\Infernum\UI\Form\Field\FieldInterface
     */
    public function setValue($value);

    /**
     * Gets the title of the field.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Sets the title of the field.
     *
     * @param string $title The title of the field
     * @return \FlameCore\Infernum\UI\Form\Field\FieldInterface
     */
    public function setTitle($title);

    /**
     * Gets the description of the field.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description of the field.
     *
     * @param string $description The description of the field
     * @return \FlameCore\Infernum\UI\Form\Field\FieldInterface
     */
    public function setDescription($description);

    /**
     * Gets the error text of the field.
     *
     * @return string
     */
    public function getErrorText();

    /**
     * Sets the error text of the field.
     *
     * @param string $errorText The error text of the field
     * @return \FlameCore\Infernum\UI\Form\Field\FieldInterface
     */
    public function setErrorText($errorText);

    /**
     * Gets the assertations of the field.
     *
     * @return array
     */
    public function getAsserts();

    /**
     * Sets the assertations of the field.
     *
     * @return array
     * @return \FlameCore\Infernum\UI\Form\Field\FieldInterface
     */
    public function setAsserts(array $asserts);

    /**
     * Retrieves the field data from the request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request
     * @return mixed
     */
    public function retrieve(Request $request);

    /**
     * Normalizes the given value.
     *
     * @param mixed $value The value to normalize
     * @return mixed
     */
    public function normalize($value);

    /**
     * Validates the given value.
     *
     * @param mixed $value The value to validate
     * @return bool
     */
    public function validate($value);
}
