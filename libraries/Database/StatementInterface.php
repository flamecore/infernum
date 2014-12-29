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
 * The Statement interface
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
interface StatementInterface
{
    /**
     * Binds a value to a parameter.
     *
     * @param mixed $parameter The 1-indexed position of the parameter
     * @param mixed $value The value to bind to the parameter
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function bind($parameter, &$value);

    /**
     * Executes the prepared statement.
     *
     * @param array $parameters An array of values with as many elements as there are bound parameters in the SQL statement being executed
     * @throws \RuntimeException on failure.
     */
    public function execute(array $parameters = null);

    /**
     * Gets the number of affected rows.
     *
     * @return int
     */
    public function getAffectedRows();

    /**
     * Returns the error code for this statement call.
     *
     * @return int Returns the error code.
     */
    public function getError();

    /**
     * Returns extended error information associated with this operation.
     *
     * @return array Returns the error information. The array consists of the following fields: SQLSTATE, error code, error message.
     */
    public function getErrorInfo();

    /**
     * Returns whether the statement was executed.
     *
     * @return bool
     */
    public function isExecuted();
}
