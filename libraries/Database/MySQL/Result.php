<?php
/**
 * Webwork
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
 * @package  FlameCore\Webwork
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

/**
 * Result set returned by a MySQL query
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Database_MySQL_Result extends Database_Base_Result
{
    /**
     * {@inheritdoc}
     */
    public function fetch($numeric = false)
    {
        return $numeric ? mysqli_fetch_row($this->result) : mysqli_fetch_assoc($this->result);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchCell($index = 0)
    {
        if (is_int($index)) {
            $row = $this->fetch(true);
        } elseif (is_string($index)) {
            $row = $this->fetch();
        } else {
            throw new InvalidArgumentException('The $index parameter must be either an integer or a string');
        }

        if (isset($row[$index])) {
            return $row[$index];
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchColumn($index = 0)
    {
        if (is_int($index)) {
            $rows = $this->fetchAll(true);
        } elseif (is_string($index)) {
            $rows = $this->fetchAll();
        } else {
            throw new InvalidArgumentException('The $index parameter must be either an integer or a string');
        }

        return array_column($rows, $index);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($numeric = false)
    {
        if (function_exists('mysqli_fetch_all')) {
            return mysqli_fetch_all($this->result, $numeric ? MYSQLI_NUM : MYSQLI_ASSOC);
        } else {
            $rows = array();

            while ($row = $this->fetch($numeric))
                $rows[] = $row;

            return $rows;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function numRows()
    {
        return mysqli_num_rows($this->result);
    }

    /**
     * {@inheritdoc}
     */
    public function hasRows()
    {
        return $this->numRows() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function numFields()
    {
        return mysqli_num_fields($this->result);
    }

    /**
     * {@inheritdoc}
     */
    public function free()
    {
        return mysqli_free_result($this->result);
    }
}
