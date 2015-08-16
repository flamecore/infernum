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

namespace FlameCore\Infernum\Entity;

use FlameCore\Infernum\Resource\AbstractResource;

/**
 * The abstract Entity class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class AbstractEntity extends AbstractResource
{
    /**
     * Updates the given columns of the record.
     *
     * @param array $columns Names and values of columns to be updated (Format: `[name => value, ...]`)
     * @return bool
     */
    protected function update($columns)
    {
        $table = static::getTable();
        $columns = array_map([__CLASS__, 'encode'], $columns);

        return $this->database->update($table, $columns, [
            'where' => "`$this->selector` = ?",
            'vars' => [$this->identifier],
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
    protected function setMultiple(array $values)
    {
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
        if (!is_array($items)) {
            throw new \InvalidArgumentException('The $items parameter must be an array');
        }

        $this->data[$key] = array_merge($this->data[$key], $items);

        return $this->update([$key => $this->data[$key]]);
    }
}
