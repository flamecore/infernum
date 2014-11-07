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

namespace FlameCore\Infernum\Entity;

use FlameCore\Infernum\System;
use FlameCore\Infernum\Resource\Resource;

/**
 * The abstract Entity class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class Entity extends Resource
{
    /**
     * Updates the given columns of the record.
     *
     * @param mixed $identifier The identifier of the record
     * @param array $columns Names and values of columns to be updated (Format: `[name => value, ...]`)
     * @return bool
     */
    protected function update($identifier, $columns)
    {
        $table = static::getTable();
        $fields = static::getFields();

        list($selector, $identifier) = static::parseIdentifier($identifier);

        if (!isset($fields[$selector]))
            throw new \DomainException(sprintf('Cannot select by "%s.%s" field as it is not defined.', $table, $selector));

        $columns = array_map([__CLASS__, 'encode'], $columns);
        return System::db()->update($table, $columns, [
            'where' => "`$selector` = {0}",
            'vars' => [$identifier],
            'limit' => 1
        ]);
    }

    /**
     * Sets the value of a data entry.
     *
     * @param string $key The key of the data entry
     * @param mixed $value The new value of the data entry
     * @return bool
     */
    protected function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this->update([$key => $value]);
    }

    /**
     * Sets the values of multiple data entries.
     *
     * @param array $values The new values of the data entries
     * @return bool
     */
    protected function setMultiple($values)
    {
        if (!is_array($values))
            throw new \InvalidArgumentException('The $values parameter must be an array');

        $this->data = array_merge($this->data, $values);

        return $this->update($values);
    }

    /**
     * Sets the value of a list item in a data entry.
     *
     * @param string $key The key of the data entry
     * @param string $subkey The key of the list item
     * @param mixed $value The new value of the list item
     * @return bool
     */
    protected function setListItem($key, $subkey, $value)
    {
        $this->data[$key][$subkey] = $value;

        return $this->update([$key => $this->data[$key]]);
    }

    /**
     * Sets the values of multiple list items in a data entry.
     *
     * @param string $key The key of the data entry
     * @param array $items The new values of the list items
     * @return bool
     */
    protected function setListItems($key, $items)
    {
        if (!is_array($items))
            throw new \InvalidArgumentException('The $items parameter must be an array');

        $this->data[$key] = array_merge($this->data[$key], $items);

        return $this->update([$key => $this->data[$key]]);
    }
}
