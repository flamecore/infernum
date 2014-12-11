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
 * The Result interface
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
interface ResultInterface
{
    /**
     * Fetches a result row as an associative or enumerated array.
     *
     * @param bool $numeric Set to TRUE to return an enumerated array (Default: FALSE)
     * @return array Returns an associative array of strings representing the fetched row if $numeric is set to FALSE.
     *   Returns an enumerated array of strings that corresponds to the fetched row otherwise.
     *   Returns NULL if there are no more rows in resultset.
     */
    public function fetch($numeric = false);

    /**
     * Fetches the value of a single cell in a result row.
     *
     * @param mixed $index The index (int or string) of the cell to fetch (Default: 0)
     * @return mixed Returns the value of the cell or NULL on failure
     */
    public function fetchCell($index = 0);

    /**
     * Fetches the values of each cell in a single column of the result set.
     *
     * @param int $index The index (int or string) of the cell to fetch (Default: 0)
     * @return array Returns the values of each cell as an array
     */
    public function fetchColumn($index = 0);

    /**
     * Fetches all result rows as an associative array or a numeric array.
     *
     * @param bool $numeric Set to TRUE to return an enumerated array (Default: FALSE)
     * @return array Returns an array of associative or numeric arrays holding result rows
     */
    public function fetchAll($numeric = false);

    /**
     * Gets the number of rows in a result.
     *
     * @return int
     */
    public function numRows();

    /**
     * Checks if the result has any rows.
     *
     * @return bool
     */
    public function hasRows();

    /**
     * Gets the number of fields in a result.
     *
     * @return int
     */
    public function numFields();

    /**
     * Frees the memory associated with the result.
     *
     * @return void
     */
    public function free();
}
