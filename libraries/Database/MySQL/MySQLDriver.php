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

namespace FlameCore\Infernum\Database\MySQL;

use FlameCore\Infernum\Database\AbstractDriver;

/**
 * This class allows you to execute operations in a MySQL database
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class MySQLDriver extends AbstractDriver
{
    /**
     * The link identifier of the connection
     *
     * @var mysqli
     */
    protected $link;

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        $this->link = @mysqli_connect($this->host, $this->user, $this->password, $this->database);

        if (mysqli_connect_errno())
            throw new \RuntimeException(sprintf('Failed connecting to the database: %s', mysqli_connect_error()));
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
        $query = $this->prepare($query, $vars);

        $result = @mysqli_query($this->link, $query);
        if ($result) {
            $this->queryCount++;

            if ($result instanceof \mysqli_result)
                return new MySQLResult($result);

            return true;
        }

        throw new \RuntimeException(sprintf('Database query failed: %s', $this->getError()));
    }

    /**
     * {@inheritdoc}
     */
    public function select($table, $columns = '*', $params = array())
    {
        if (is_array($columns))
            $columns = '`'.implode('`, `', $columns).'`';

        $sql = 'SELECT '.$columns.' FROM `<PREFIX>'.$table.'`';

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
        $columns = array();
        $values = array();

        foreach ($data as $column => $value) {
            $columns[] = '`'.$column.'`';
            $values[]  = $this->encode($value);
        }

        $sql = 'INSERT INTO `<PREFIX>'.$table.'` ('.implode(', ', $columns).') VALUES('.implode(', ', $values).')';
        return $this->query($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function update($table, $data, $params = array())
    {
        $dataset = array();

        foreach ($data as $key => $value) {
            $value = $this->encode($value);
            $dataset[] = '`'.$key.'` = '.$value;
        }

        $sql = 'UPDATE `<PREFIX>'.$table.'` SET '.implode(', ', $dataset);

        if (isset($params['where']))
            $sql .= ' WHERE '.$params['where'];
        if (isset($params['limit']))
            $sql .= ' LIMIT '.$params['limit'];

        return $this->query($sql, isset($params['vars']) ? $params['vars'] : null);
    }

    /**
     * {@inheritdoc}
     */
    public function batch($statements)
    {
        if (is_array($statements))
            $statements = implode(';', $statements);

        $statements = $this->prepare($statements);

        if (mysqli_multi_query($this->link, $statements)) {
            $i = 1;
            do {
                $i++;
            } while (mysqli_next_result($this->link));
        }

        if (mysqli_errno($this->link))
            throw new \RuntimeException(sprintf('Batch execution prematurely ended at statement %d: %s', $i, mysqli_error($this->link)));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function import($file)
    {
        if (!is_file($file) || !is_readable($file))
            throw new \LogicException(sprintf('File "%s" does not exist or is not readable.', $file));

        $sql = trim(file_get_contents($file));
        $sql = preg_replace('@(([\'"`]).*?[^\\\]\2)|((?:\#|--).*?$|/\*(?:[^/*]|/(?!\*)|\*(?!/)|(?R))*\*\/)\s*|(?<=;)\s+@ms', '$1', $sql);

        return $this->batch($sql);
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
    public function getCharset()
    {
        return mysqli_character_set_name($this->link);
    }

    /**
     * {@inheritdoc}
     */
    public function setCharset($charset)
    {
        return mysqli_set_charset($this->link, (string) $charset);
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        return mysqli_error($this->link);
    }

    /**
     * {@inheritdoc}
     */
    public function escape($string)
    {
        $string = mysqli_real_escape_string($this->link, $string);
        $string = addcslashes($string, '%_');

        return $string;
    }
}
