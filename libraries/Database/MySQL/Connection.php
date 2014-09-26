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

namespace FlameCore\Webwork\Database\MySQL;

use FlameCore\Webwork\Database\Base\Connection as BaseConnection;

/**
 * This class allows you to execute operations in a MySQL database
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Connection extends BaseConnection
{
    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        $this->link = @mysqli_connect($this->host, $this->user, $this->password, $this->database);

        if (mysqli_connect_errno())
            trigger_error('Failed connecting to the database: '.mysqli_connect_error(), E_USER_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        mysqli_close($this->link);
    }

    /**
     * {@inheritdoc}
     */
    public function query($query, $vars = null)
    {
        $query = $this->prepareQuery($query, $vars);

        $result = @mysqli_query($this->link, $query);
        if ($result) {
            $this->queryCount++;

            if ($result instanceof \MySQLi_Result)
                return new Result($result);

            return true;
        }

        trigger_error('Database query failed: '.$this->getError(), E_USER_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function select($table, $columns = '*', $params = array())
    {
        $sql = 'SELECT '.$columns.' FROM `'.$table.'`';

        if (isset($params['where']))
            $sql .= ' WHERE '.$params['where'];
        if (isset($params['limit']))
            $sql .= ' LIMIT '.$params['limit'];
        if (isset($params['group']))
            $sql .= ' GROUP BY '.$params['group'];
        if (isset($params['order']))
            $sql .= ' ORDER BY '.$params['order'];

        return $this->query($sql, isset($params['vars']) ? $params['vars'] : null);
    }

    /**
     * {@inheritdoc}
     */
    public function insert($table, $data)
    {
        foreach ($data as $column => $value) {
            $columns[] = '`'.$column.'`';
            $values[]  = $this->prepareValue($value);
        }

        $sql = 'INSERT INTO `'.$table.'` ('.implode(', ', $columns).') VALUES('.implode(', ', $values).')';
        return $this->query($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function update($table, $data, $params = array())
    {
        foreach ($data as $key => $value) {
            $value = $this->prepareValue($value);
            $dataset[] = '`'.$key.'` = '.$value;
        }

        $sql = 'UPDATE `'.$table.'` SET '.implode(', ', $dataset);

        if (isset($params['where']))
            $sql .= ' WHERE '.$params['where'];
        if (isset($params['limit']))
            $sql .= ' LIMIT '.$params['limit'];

        return $this->query($sql, isset($params['vars']) ? $params['vars'] : null);
    }

    /**
     * {@inheritdoc}
     */
    public function importDump($file, $vars = null)
    {
        $dumpContent = file_get_contents($file);
        $queries = preg_split('/;\s*$/', $dumpContent);

        $this->beginTransaction();

        foreach ($queries as $query) {
            $query = trim($query);
            if ($query == '' || substr($query, 0, 2) == '--')
                continue;
            $this->query($query, $vars);
        }

        $this->endTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function affectedRows()
    {
        return mysqli_affected_rows($this->link);
    }

    /**
     * {@inheritdoc}
     */
    public function insertID()
    {
        return mysqli_insert_id($this->link);
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        mysqli_autocommit($this->link, false);
        $this->inTransaction = true;
    }

    /**
     * {@inheritdoc}
     */
    public function endTransaction()
    {
        mysqli_autocommit($this->link, true);
        $this->inTransaction = false;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        if ($this->inTransaction) {
            return mysqli_commit($this->link);
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        if ($this->inTransaction) {
            return mysqli_rollback($this->link);
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function quote($string)
    {
        return mysqli_real_escape_string($this->link, $string);
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        return mysqli_error($this->link);
    }
}
