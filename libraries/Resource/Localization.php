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
 * Object describing a locale
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Localization extends DatabaseResource
{
    /**
     * Fetches the data of the locale
     *
     * @param string $identifier The ID of the locale
     */
    public function __construct($identifier)
    {
        $sql = 'SELECT * FROM @PREFIX@locales WHERE id = {0} LIMIT 1';
        $result = System::db()->query($sql, [$identifier]);

        if ($result->hasRows()) {
            $this->loadData($result->fetch(), [
                'id' => 'string',
                'name' => 'string',
                'text_direction' => 'string',
                'number_sep_decimal' => 'string',
                'number_sep_thousand' => 'string',
                'fmt_money' => 'string',
                'fmt_time' => 'string',
                'fmt_date_short' => 'string',
                'fmt_date_medium' => 'string',
                'fmt_date_long' => 'string'
            ]);
        } else {
            throw new \Exception(sprintf('Locale does not exist (id = %s)', $identifier));
        }
    }

    /**
     * Returns the locale's ID
     *
     * @return int
     */
    public function getID()
    {
        return $this->get('id');
    }

    /**
     * Returns the name of the locale
     *
     * @return string
     */
    public function getName()
    {
        return $this->get('name');
    }

    /**
     * Sets the name of the locale
     *
     * @param string $name The new name
     * @return bool
     */
    public function setName($name)
    {
        return $this->set('name', $name);
    }

    /**
     * Returns the text direction of the locale
     *
     * @return string
     */
    public function getTextDirection()
    {
        return $this->get('text_direction');
    }

    /**
     * Returns the number separators of the locale
     *
     * @return array
     */
    public function getNumberSeparators()
    {
        return [
            'decimal'  => $this->get('number_sep_decimal'),
            'thousand' => $this->get('number_sep_thousand')
        ];
    }

    /**
     * Returns the money format of the locale
     *
     * @return string
     */
    public function getMoneyFormat()
    {
        return $this->get('fmt_money');
    }

    /**
     * Returns the time format of the locale
     *
     * @return string
     */
    public function getTimeFormat()
    {
        return $this->get('fmt_time');
    }

    /**
     * Returns the date format of the locale
     *
     * @param int $length The date length (1 = short [default], 2 = medium, 3 = long)
     * @return string
     */
    public function getDateFormat($length = 1)
    {
        if ($length >= 3) {
            return $this->get('fmt_date_long');
        } elseif ($length == 2) {
            return $this->get('fmt_date_medium');
        } else {
            return $this->get('fmt_date_short');
        }
    }

    /**
     * Updates the given columns in the database table
     *
     * @param array $columns Names and values of columns to be updated (Format: [name => value, ...])
     * @return bool
     */
    protected function update($columns)
    {
        return System::db()->update('@PREFIX@locales', $columns, [
            'where' => 'id = {0}',
            'vars' => [$this->get('id')]
        ]);
    }

    /**
     * Checks whether or not a locale with given ID exists
     *
     * @param string $id The ID of the locale
     * @return bool
     */
    public static function exists($id)
    {
        $sql = 'SELECT id FROM @PREFIX@locales WHERE id = {0} LIMIT 1';
        $result = System::db()->query($sql, [$id]);

        return $result->hasRows();
    }

    /**
     * Returns a list of available locales
     *
     * @return array
     */
    public static function getAvailable()
    {
        $cache = new Cache('locales/list');
        return $cache->data(function () {
            return System::db()->select('@PREFIX@locales', 'id')->fetchColumn();
        });
    }
}
