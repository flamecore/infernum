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

namespace FlameCore\Infernum\Entity;

/**
 * Object describing a user group
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class UserGroup extends Entity
{
    /**
     * Fetches the data of the user group
     *
     * @param int|string $identifier The ID (int) or name (string) of the user group
     */
    public function __construct($identifier)
    {
        if (is_string($identifier)) {
            $selector = 'name';
        } elseif (is_int($identifier)) {
            $selector = 'id';
        } else {
            trigger_error('Invalid user group identifier given', E_USER_ERROR);
        }

        $sql = sprintf('SELECT * FROM @PREFIX@usergroups WHERE %s = {0} LIMIT 1', $selector);
        $result = System::db()->query($sql, [$identifier]);

        if ($result->hasRows()) {
            $this->loadData($result->fetch(), [
                'id' => 'int',
                'name' => 'string',
                'title' => 'string',
                'accesslevel' => 'int'
            ]);
        } else {
            throw new \Exception(sprintf('User group does not exist (%s = %s)', $selector, $identifier));
        }
    }

    /**
     * Returns the groups's ID
     *
     * @return int
     */
    public function getID()
    {
        return $this->get('id');
    }

    /**
     * Returns the name of the group
     *
     * @return string
     */
    public function getName()
    {
        return $this->get('name');
    }

    /**
     * Returns the title of the group
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->get('title');
    }

    /**
     * Sets the title of the group
     *
     * @param string $title The new title
     */
    public function setTitle($title)
    {
        $this->set('title', $title);
    }

    /**
     * Returns the access level of the group
     *
     * @return int
     */
    public function getAccessLevel()
    {
        return $this->get('accesslevel');
    }

    /**
     * Sets the access level of the group
     *
     * @param int $level The new access level
     */
    public function setAccessLevel($level)
    {
        $this->set('accesslevel', $level);
    }

    /**
     * Checks if the group is hierarchically equal or superior to the given group
     *
     * @param int|string $mingroup Require at least this user group. Accepts ID (int) or name (string) of the group.
     * @return bool
     */
    public function isAuthorized($mingroup)
    {
        $mingroup = new self($mingroup);
        $minlevel = $mingroup->getAccessLevel();

        return $this->get('accesslevel') >= $minlevel;
    }

    /**
     * Updates the given columns in the database table
     *
     * @param array $columns Names and values of columns to be updated (Format:  [name => value, ...]`)
     * @return bool
     */
    protected function update($columns)
    {
        return System::db()->update('@PREFIX@usergroups', $columns, [
            'where' => 'id = {0}',
            'vars' => [$this->get('id')]
        ]);
    }

    /**
     * Checks whether a user group with given ID exists
     *
     * @param    int      $id   The ID of the user group
     * @return   bool
     */
    public static function exists($id)
    {
        $sql = 'SELECT id FROM @PREFIX@usergroups WHERE id = {0} LIMIT 1';
        $result = System::db()->query($sql, [$id]);

        return $result->hasRows();
    }
}
