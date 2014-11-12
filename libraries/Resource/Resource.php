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

/**
 * The abstract Resource class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class Resource
{
    /**
     * The data of the resource
     *
     * @var array
     */
    protected $data = array();

    /**
     * Fetches the data of the record. This method must retrieve the data.
     *
     * @param mixed $identifier The identifier of the record
     */
    abstract public function __construct($identifier);

    /**
     * Checks wheter or not the record with given identifier exists.
     *
     * @param int $identifier The identifier of the record
     * @return bool
     */
    abstract public static function exists($identifier);

    /**
     * Loads all data with given type mapping.
     *
     * @param array $data The data from database
     * @param array $typemap The type mapping
     */
    abstract protected function loadData(Array $data, Array $typemap);

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
}
