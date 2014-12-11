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

namespace FlameCore\Infernum\Database;

/**
 * This class allows you to execute operations in a database
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class AbstractDriver implements DriverInterface
{
    /**
     * The database server host
     *
     * @var string
     */
    protected $host;

    /**
     * The username for authenticating at the database server
     *
     * @var string
     */
    protected $user;

    /**
     * The password for authenticating at the database server
     *
     * @var string
     */
    protected $password;

    /**
     * The name of the database
     *
     * @var string
     */
    protected $database;

    /**
     * The prefix of the database tables
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * The number of executed queries
     *
     * @var int
     */
    protected $queryCount = 0;

    /**
     * Currently in transaction?
     *
     * @var bool
     */
    protected $inTransaction = false;

    /**
     * Constructor
     *
     * @param string $host The database server host
     * @param string $user The username for authenticating at the database server
     * @param string $password The password for authenticating at the database server
     * @param string $database The name of the database
     */
    public function __construct($host, $user, $password, $database)
    {
        $this->host = (string) $host;
        $this->user = (string) $user;
        $this->password = (string) $password;
        $this->database = (string) $database;

        $this->connect();
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = (string) $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryCount()
    {
        return $this->queryCount;
    }

    /**
     * Encodes a PHP value for use in a SQL statement.
     *
     * @param mixed $value The value to encode
     * @return string
     */
    protected function encode($value)
    {
        if (is_numeric($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return (int) $value;
        } elseif (is_string($value)) {
            return "'".$this->escape($value)."'";
        } elseif (is_array($value)) {
            return "'".$this->escape(implode(',', $value))."'";
        } elseif (is_object($value)) {
            return "'".$this->escape((string) $value)."'";
        } else {
            return 'NULL';
        }
    }

    /**
     * Prepares a SQL statement. Replaces `<HOST>`, `<USER>`, `<DATABASE>`, `<PREFIX>` and `{variables}`, if neccessary.
     *
     * @param string $statement The SQL statement to prepare
     * @param array $vars An array of values replacing the variables. Only neccessary if you're using variables.
     * @return string
     */
    protected function prepare($statement, $vars = null)
    {
        $replace = array(
            '<HOST>' => $this->host,
            '<USER>' => $this->user,
            '<DATABASE>' => $this->database,
            '<PREFIX>' => $this->prefix
        );

        if (is_array($vars)) {
            foreach ($vars as $key => $value) {
                $replace['{'.$key.'}'] = $this->encode($value);
            }
        }

        return strtr($statement, $replace);
    }
}
