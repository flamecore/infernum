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

namespace FlameCore\Infernum\Resource;

use FlameCore\Infernum\Database\DriverInterface;

/**
 * The abstract Resource class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class AbstractResource
{
    /**
     * @var \FlameCore\Infernum\Database\DriverInterface
     */
    protected $database;

    /**
     * @var string
     */
    protected $selector;

    /**
     * @var mixed
     */
    protected $identifier;

    /**
     * The data of the resource
     *
     * @var array
     */
    protected $data = array();

    /**
     * Fetches the data of the record.
     *
     * @param mixed $identifier The identifier of the record
     * @param \FlameCore\Infernum\Database\DriverInterface $database The database connection to use
     */
    public function __construct($identifier, DriverInterface $database)
    {
        $table = static::getTable();
        $fields = static::getFields();
        $columns = array_keys($fields);

        $parsed = static::parseIdentifier($identifier);

        if (!is_array($parsed) || count($parsed) < 2) {
            throw new \UnexpectedValueException(sprintf('Method %s::parseIdentifier() must return an array with at least two elements.', __CLASS__));
        }

        list($selector, $identifier) = $parsed;

        if (!isset($fields[$selector])) {
            throw new \DomainException(sprintf('Cannot select by "%s.%s" field as it is not defined.', $table, $selector));
        }

        $this->database = $database;
        $this->selector = $selector;
        $this->identifier = $identifier;

        $result = $database->select($table, $columns, [
            'where' => "`$selector` = ?",
            'vars' => [$identifier],
            'limit' => 1
        ]);

        if (!$result->hasRows()) {
            throw new \DomainException(sprintf('Resource does not exist. (%s.%s = %s)', $table, $selector, $identifier));
        }

        $data = $result->fetch();

        $this->data = array();
        foreach ($fields as $field => $type) {
            $this->data[$field] = self::decode($data[$field], $fields[$field]);
        }
    }

    /**
     * Checks whether or not the record with given identifier exists.
     *
     * @param mixed $identifier The identifier of the record
     * @param \FlameCore\Infernum\Database\DriverInterface $database The database connection to use
     * @return bool
     */
    public static function exists($identifier, DriverInterface $database)
    {
        $table = static::getTable();
        $fields = static::getFields();

        $parsed = static::parseIdentifier($identifier);

        if (!is_array($parsed) || count($parsed) < 2) {
            throw new \UnexpectedValueException(sprintf('Method %s::parseIdentifier() must return an array with at least two elements.', __CLASS__));
        }

        list($selector, $identifier) = $parsed;

        if (!isset($fields[$selector])) {
            throw new \DomainException(sprintf('Cannot select by "%s.%s" field as it is not defined.', $table, $selector));
        }

        $result = $database->select($table, 'id', [
            'where' => "`$selector` = ?",
            'vars' => [$identifier],
            'limit' => 1
        ]);

        return $result->hasRows();
    }

    /**
     * Returns a list of available records.
     *
     * @param \FlameCore\Infernum\Database\DriverInterface $database The database connection to use
     * @return array
     */
    public static function listAll(DriverInterface $database)
    {
        $table = static::getTable();
        $fields = static::getFields();
        $keyName = static::getKeyName();

        if (!isset($fields[$keyName])) {
            throw new \DomainException(sprintf('Cannot select by "%s.%s" field as it is not defined.', $table, $keyName));
        }

        $items = array();

        $result = $database->select($table, $keyName);
        while ($data = $result->fetch()) {
            $items[] = self::decode($data[$keyName], $fields[$keyName]);
        }

        return $items;
    }

    /**
     * Fetches all available records.
     *
     * @param \FlameCore\Infernum\Database\DriverInterface $database The database connection to use
     * @return array
     */
    public static function fetchAll(DriverInterface $database)
    {
        $table = static::getTable();
        $fields = static::getFields();
        $columns = array_keys($fields);
        $keyName = static::getKeyName();

        if (!isset($fields[$keyName])) {
            throw new \DomainException(sprintf('Cannot select by "%s.%s" field as it is not defined.', $table, $keyName));
        }

        $items = array();

        $result = $database->select($table, $columns);
        while ($data = $result->fetch()) {
            $key = self::decode($data[$keyName], $fields[$keyName]);

            $items[$key] = array();
            foreach ($fields as $field => $type) {
                $items[$key][$field] = self::decode($data[$field], $fields[$field]);
            }
        }

        return $items;
    }

    /**
     * Returns the value of a data entry.
     *
     * @param string $key The key of the data entry
     * @return mixed
     */
    protected function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : false;
    }

    /**
     * Returns the value of a list item in a data entry.
     *
     * @param string $key The key of the data entry
     * @param string $subkey The key of the list item
     * @return mixed
     */
    protected function getListItem($key, $subkey)
    {
        return isset($this->data[$key][$subkey]) ? $this->data[$key][$subkey] : false;
    }

    /**
     * Encodes the given value for usage in a database statement.
     *
     * @param mixed $value The value
     * @return mixed
     */
    protected static function encode($value)
    {
        if (is_array($value)) {
            return serialize($value);
        } else {
            return $value;
        }
    }

    /**
     * Decodes the value as given datatype.
     *
     * @param string $value The value
     * @param string $type The datatype of the value
     * @return mixed
     */
    protected static function decode($value, $type)
    {
        if ($type == 'int') {
            return (int) $value;
        } elseif ($type == 'float') {
            return (float) $value;
        } elseif ($type == 'bool') {
            return (bool) $value;
        } elseif ($type == 'datetime') {
            return new \DateTime($value);
        } elseif ($type == 'array') {
            return unserialize($value);
        } else {
            return $value;
        }
    }

    /**
     * Parses the identifier and returns corresponding selector and formatted identifier. Defaults to key as selector.
     *
     * @param mixed $identifier The identifier of the record
     * @return array Returns selector and formatted identifier as array with the format: `[selector, identifier]`.
     */
    protected static function parseIdentifier($identifier)
    {
        return array(static::getKeyName(), $identifier);
    }

    /**
     * Gets the table to use.
     *
     * @return string
     */
    abstract protected static function getTable();

    /**
     * Gets the key name.
     *
     * @return string
     */
    abstract protected static function getKeyName();

    /**
     * Gets the list of fields to use with their datatype.
     *
     * @return array Returns an array with the format: `[fieldname => datatype, ...]`.
     */
    abstract protected static function getFields();
}
