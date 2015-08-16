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

namespace FlameCore\Infernum;

/**
 * The Container class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Container
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $items = array();

    /**
     * @var array
     */
    protected $locked = array();

    /**
     * @var array
     */
    protected $typemap = array();

    /**
     * Creates the container.
     *
     * @param string $name The name
     * @param array $typemap The typemap
     * @throws \InvalidArgumentException if the name is invalid.
     */
    public function __construct($name, array $typemap = [])
    {
        $name = (string) $name;

        if ($name === '') {
            throw new \InvalidArgumentException('The container name must not be empty');
        }

        $this->name = $name;
        $this->typemap = $typemap;
    }

    /**
     * Returns the value of the item with given name.
     *
     * @param string $name The name of the item
     * @return mixed
     */
    public function get($name)
    {
        $name = (string) $name;

        return isset($this->items[$name]) ? $this->items[$name] : null;
    }

    /**
     * Returns whether an item with given name is defined.
     *
     * @param string $name The name of the item
     * @return bool
     */
    public function has($name)
    {
        $name = (string) $name;

        return isset($this->items[$name]);
    }

    /**
     * Assigns a value to the item with given name.
     *
     * @param string $name The name of the item
     * @param mixed $value The value to assign
     * @param bool $lock Lock the item
     * @throws \InvalidArgumentException if an item with empty name should be set or if the value for a typehinted item is invalid.
     * @throws \LogicException if a locked item should be overridden, which is not allowed.
     */
    public function set($name, $value, $lock = false)
    {
        $name = (string) $name;

        if ($name === '') {
            throw new \InvalidArgumentException(sprintf('Cannot set item with empty name in %s container.', $this->name));
        }

        if ($this->has($name) && in_array($name, $this->locked)) {
            throw new \LogicException(sprintf('Cannot override locked item "%s" in %s container.', $name, $this->name));
        }

        if (isset($this->typemap[$name])) {
            $type = $this->typemap[$name];
            if ($type[0] == '\\') {
                $className = substr($type, 1);
                if (!$value instanceof $className) {
                    throw new \InvalidArgumentException(sprintf('Value for item "%s" in %s container must be an instance of %s class, but %s given.', $name, $this->name, $className, $this->getType($value)));
                }
            } else {
                $actualType = $this->getType($value);
                if ($actualType != $type) {
                    throw new \InvalidArgumentException(sprintf('Value for item "%s" in %s container must be of type %s, but %s given.', $name, $this->name, $type, $actualType));
                }
            }
        }

        $this->items[$name] = $value;

        if ((bool) $lock) {
            $this->locked[] = $name;
        }
    }

    /**
     * Removes the value from item with given name.
     *
     * @param string $name The name of the item
     * @throws \LogicException if the given item is a locked item, which cannot be unset.
     */
    public function remove($name)
    {
        $name = (string) $name;

        if ($name === '') {
            throw new \InvalidArgumentException(sprintf('Cannot unset item with empty name in %s container.', $this->name));
        }

        if ($this->has($name) && in_array($name, $this->locked)) {
            throw new \LogicException(sprintf('Cannot unset locked item "%s" in %s container.', $name, $this->name));
        }

        unset($this->items[$name]);
    }

    /**
     * Returns the type of the value.
     *
     * @param mixed $value The value
     * @return string
     */
    protected function getType($value)
    {
        if (is_object($value)) {
            return get_class($value);
        } elseif (is_null($value)) {
            return 'null';
        } elseif (is_string($value)) {
            return 'string';
        } elseif (is_array($value)) {
            return 'array';
        } elseif (is_int($value)) {
            return 'integer';
        } elseif (is_bool($value)) {
            return 'boolean';
        } elseif (is_float($value)) {
            return 'float';
        } elseif (is_resource($value)) {
            return 'resource';
        } else {
            return 'unknown';
        }
    }
}
