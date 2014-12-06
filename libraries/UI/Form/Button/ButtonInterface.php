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
