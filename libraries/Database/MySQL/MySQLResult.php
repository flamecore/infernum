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

namespace FlameCore\Infernum\Database\MySQL;

use FlameCore\Infernum\Database\AbstractResult;

/**
 * Result set returned by a MySQL query
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class MySQLResult extends AbstractResult
{
    /**
     * The result object returned by the corresponding query
     *
     * @var \mysqli_result
     */
    protected $result;

    /**
     * Constructor
     *
     * @param \mysqli_result $result The result object returned by the corresponding query
     */
    public function __construct(\mysqli_result $result)
    {
        $this->result = $result;
    }

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
            throw new \InvalidArgumentException('The $index parameter must be either an integer or a string.');
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
            throw new \InvalidArgumentException('The $index parameter must be either an integer or a string.');
        }

        return array_column($rows, $index);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($numeric = false)
    {
        $rows = array();

        while ($row = $this->fetch($numeric)) {
            $rows[] = $row;
        }

        return $rows;
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
