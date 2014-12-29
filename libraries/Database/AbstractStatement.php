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

namespace FlameCore\Infernum\Database;

/**
 * The abstract Statement class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class AbstractStatement
{
    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @var bool
     */
    protected $executed = false;

    /**
     * {@inheritdoc}
     */
    public function bind($parameter, &$value)
    {
        $index = $parameter - 1;

        if (!isset($this->parameters[$index])) {
            throw new \OutOfRangeException(sprintf('Parameter position %d does not exist.', $parameter));
        }

        $this->parameters[$index] = &$value;
    }

    /**
     * {@inheritdoc}
     */
    public function isExecuted()
    {
        return $this->executed;
    }

    /**
     * Encodes a PHP value for use in a SQL statement.
     *
     * @param mixed $value The value to encode
     * @return string
     */
    protected function encode($value)
    {
        if (is_bool($value)) {
            return (int) $value;
        } elseif (is_scalar($value)) {
            return $value;
        } elseif ($value instanceof DateTime) {
            return $value->format('Y-m-d H:i:s');
        } elseif (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        } elseif (is_array($value)) {
            return implode(',', $value);
        } else {
            throw new \InvalidArgumentException(sprintf('Cannot encode value of type %s.', gettype($value)));
        }
    }
}
