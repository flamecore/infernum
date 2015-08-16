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

namespace FlameCore\Infernum\Template;

/**
 * The Engine interface
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
interface EngineInterface
{
    /**
     * Renders the given template
     *
     * @param string $name The name of the template
     * @param array $variables The variables to use
     * @return string
     * @throws \FlameCore\Infernum\Template\NotFoundException
     * @throws \FlameCore\Infernum\Template\BadNameException
     */
    public function render($name, array $variables);
}
