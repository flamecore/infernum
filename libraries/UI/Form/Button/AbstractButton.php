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
 * Base class for form buttons.
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class AbstractButton implements ButtonInterface
{
    /**
     * The title of the form button
     *
     * @var string
     */
    protected $title;

    /**
     * {@inheritdoc}
     */
    public function __construct(Form $form, $title, array $params = [])
    {
        $this->form = $form;
        $this->setTitle($title);

        $this->initialize($params);
    }

    /**
     * Initializes the form button.
     *
     * @param array $params The form button parameters
     */
    public function initialize(array $params)
    {
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getType();

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
}
