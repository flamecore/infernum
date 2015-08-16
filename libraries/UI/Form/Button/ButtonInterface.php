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

namespace FlameCore\Infernum\UI\Form\Button;

use FlameCore\Infernum\UI\Form\Form;

/**
 * The Button interface
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
interface ButtonInterface
{
    /**
     * Constructor
     *
     * @param \FlameCore\Infernum\UI\Form\Form $form The form object
     * @param string $title The title of the button
     * @param array $params The form button parameters
     */
    public function __construct(Form $form, $title, array $params);

    /**
     * Gets the type of this button
     *
     * @return string
     */
    public function getType();

    /**
     * Gets the form object.
     *
     * @return \FlameCore\Infernum\UI\Form\Form
     */
    public function getForm();

    /**
     * Gets the title of the button.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Sets the title of the button.
     *
     * @param string $title The title of the button
     * @return \FlameCore\Infernum\UI\Form\Button\ButtonInterface
     */
    public function setTitle($title);
}
