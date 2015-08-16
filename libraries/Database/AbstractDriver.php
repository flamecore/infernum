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
    public function getQueryCount()
    {
        return $this->queryCount;
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
     * Interpolates a SQL statement. Replaces `<HOST>`, `<USER>`, `<DATABASE>`, `<PREFIX>` if necessary.
     *
     * @param string $statement The SQL statement to interpolate
     * @return string
     */
    protected function interpolate($statement)
    {
        $replace = array(
            '<HOST>' => $this->host,
            '<USER>' => $this->user,
            '<DATABASE>' => $this->database,
            '<PREFIX>' => $this->prefix
        );

        return strtr($statement, $replace);
    }
}
