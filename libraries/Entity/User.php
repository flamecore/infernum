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

namespace FlameCore\Infernum\Entity;

/**
 * Object describing a registered user.
 *
 * The identifier can be the ID (int) or username (string) of the user.
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class User extends AbstractEntity
{
    /**
     * Returns the user's ID
     *
     * @return int
     */
    public function getID()
    {
        return $this->get('id');
    }

    /**
     * Returns the username of the user
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->get('username');
    }

    /**
     * Updates the username of the user
     *
     * @param string $username The new ussername
     */
    public function setUsername($username)
    {
        $this->set('username', $username);
    }

    /**
     * Returns the email address of the user
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->get('email');
    }

    /**
     * Updates the email address of the user
     *
     * @param string $email The new email address
     */
    public function setEmail($email)
    {
        $this->set('email', $email);
    }

    /**
     * Checks if the given password matches the user's password hash
     *
     * @param string $password The password to verify
     * @return bool
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->get('password'));
    }

    /**
     * Updates the password of the user
     *
     * @param string $password The new password
     */
    public function setPassword($password)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->set('password', $hash);
    }

    /**
     * Returns the ID of the group that the user belongs to
     *
     * @return int
     */
    public function getGroupID()
    {
        return $this->get('group');
    }

    /**
     * Updates the group of the user
     *
     * @param int|string $group The ID (int) or name (string) of the user group
     */
    public function setGroup($group)
    {
        $group = new UserGroup($group, $this->database);
        $this->set('group', $group);
    }

    /**
     * Returns the value of the given user profile field. If no $key is given, the values of all fields
     *   are returned as an array.
     *
     * @param string $key The key of the profile field (optional)
     * @return mixed
     */
    public function getProfile($key = null)
    {
        return isset($key) ? $this->getListItem('profile', $key) : $this->get('profile');
    }

    /**
     * Updates one or more items in the user profile
     *
     * @param mixed $param1   The name of a single field (string) or pairs of names and values of multiple
     *   fields (array in the format [name => value, ...]) to be updated
     * @param mixed $param2   The new value of the field to be updated (only if parameter 1 is used for
     *   the field name)
     * @return bool
     */
    public function setProfile($param1, $param2 = null)
    {
        if (is_array($param1)) {
            // Update multiple columns
            $this->setListItems('profile', $param1);
        } elseif (is_string($param1) && isset($param2)) {
            // Update a single column
            $this->setListItem('profile', $param1, $param2);
        } else {
            throw new \InvalidArgumentException('The first parameter must be an array or a string together with the second parameter.');
        }
    }

    /**
     * Returns the last activity time of the user
     *
     * @return DateTime
     */
    public function getLastActive()
    {
        return $this->get('lastactive');
    }

    /**
     * Updates the last activity time of the user
     *
     * @param DateTime $time The new last activity time (Default: now)
     */
    public function setLastActive(DateTime $time = null)
    {
        $this->set('lastactive', $time ?: new DateTime);
    }

    /**
     * Checks if the user is online
     *
     * @return bool
     */
    public function isOnline()
    {
        $lastactive = $this->get('lastactive');
        $threshold = System::setting('session.online_threshold', 600);

        // Check if the last activity time is within the threshold
        return $lastactive->diff(new DateTime)->format('%s') <= $threshold;
    }

    /**
     * Checks if the user's group is hierarchically equal or superior to the given group
     *
     * @param int|string $mingroup Require at least this user group. Accepts ID (int) or name (string) of the group.
     * @return bool
     */
    public function isAuthorized($mingroup)
    {
        $group = new UserGroup($this->get('group'), $this->database);
        return $group->isAuthorized($mingroup);
    }

    /**
     * Parses the user identifier.
     *
     * @param int|string $identifier The ID (int) or username (string) of the user
     * @return array
     */
    protected static function parseIdentifier($identifier)
    {
        if (is_string($identifier)) {
            $selector = 'username';
        } elseif (is_int($identifier)) {
            $selector = 'id';
        } else {
            throw new \InvalidArgumentException('Invalid user identifier given.');
        }

        return array($selector, $identifier);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getTable()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getKeyName()
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getFields()
    {
        return array(
            'id' => 'int',
            'username' => 'string',
            'email' => 'string',
            'password' => 'string',
            'group' => 'int',
            'lastactive' => 'datetime',
            'profile' => 'array'
        );
    }
}
