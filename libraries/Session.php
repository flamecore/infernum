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

namespace FlameCore\Infernum;

use FlameCore\Infernum\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Simple user session manager
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Session
{
    /**
     * The ID of the current session
     *
     * @var string
     */
    private $id;

    /**
     * The user who is assigned to the current session
     *
     * @var User
     */
    private $user;

    /**
     * The stored session data
     *
     * @var array
     */
    private $data = array();

    /**
     * The lifetime of the session in seconds
     *
     * @var int
     */
    private $lifetime = 3600;

    private $app;

    /**
     * Initializes the session system.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request
     * @param \FlameCore\Infernum\Application $app The application context
     * @return \FlameCore\Infernum\Session Returns the new Session object.
     */
    public static function init(Request $request, Application $app)
    {
        // Clean up sessions table from expired sessions before proceeding
        $sql = 'DELETE FROM @PREFIX@sessions WHERE expire <= {0}';
        $app['db']->query($sql, [date('Y-m-d H:i:s')]);

        $sid = $request->cookies->get($app->getCookieName('session'), uniqid(time(), true));

        $session = new self($sid, $app);
        $session->refresh();

        return $session;
    }

    /**
     * Generates a Session object.
     *
     * @param string $sid The ID of the session
     * @param \FlameCore\Infernum\Application $app The application context
     */
    public function __construct($sid, Application $app)
    {
        $sql = 'SELECT * FROM @PREFIX@sessions WHERE id = {0} AND expire > {1} LIMIT 1';
        $result = $app['db']->query($sql, [$sid, date('Y-m-d H:i:s')]);

        if ($result->hasRows()) {
            $info = $result->fetch();

            $this->id = $info['id'];
            $this->lifetime = (int) $info['lifetime'];

            if ($info['user'] > 0)
                $this->user = new User((int) $info['user'], $app['db']);

            if (!empty($info['data']))
                $this->data = unserialize($info['data']);
        } else {
            $this->id = $sid;
            $this->lifetime = $app->setting('session.lifetime', $this->lifetime);

            // Create a new session
            $sql = 'INSERT INTO @PREFIX@sessions (id, expire) VALUES({0}, {1})';
            $app['db']->query($sql, [$this->id, $this->getExpire()->format('Y-m-d H:i:s')]);
        }

        $this->app = $app;
    }

    /**
     * Returns the ID of the session
     *
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Returns the assigned user. FALSE is returned if no user is assigned.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user ?: false;
    }

    /**
     * Checks if a user is assigned to the session
     *
     * @return bool
     */
    public function isUserAssigned()
    {
        return isset($this->user);
    }

    /**
     * Assigns a user to the session
     *
     * @param mixed $user The ID (int) or username (string) of the user
     * @throws Exception
     */
    public function assignUser($user)
    {
        $user = new User($user);

        $sql = 'UPDATE @PREFIX@sessions SET user = {0} WHERE id = {1} LIMIT 1';
        $this->app['db']->query($sql, [$user->getID(), $this->id]);

        $this->user = $user;
    }

    /**
     * Returns the lifetime of the session
     *
     * @return int
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Sets the lifetime of the session
     *
     * @param int $time The new session lifetime in seconds
     */
    public function setLifetime($time)
    {
        $this->lifetime = (int) $time;

        $sql = 'UPDATE @PREFIX@sessions SET lifetime = {0}, expire = {1} WHERE id = {2} LIMIT 1';
        $this->app['db']->query($sql, [$this->lifetime, $this->getExpire()->format('Y-m-d H:i:s'), $this->id]);
    }

    /**
     * Returns the expiration time of the session
     *
     * @return DateTime
     */
    public function getExpire()
    {
        $time = new \DateTime();
        return $time->add(new \DateInterval("PT{$this->lifetime}S"));
    }

    /**
     * Reads data from the currently running session
     *
     * @param string $key The key of the data entry
     * @return mixed
     */
    public function read($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : false;
    }

    /**
     * Stores data to the session
     *
     * @param string $key The key of the data entry
     * @param mixed $value The value of the data entry
     */
    public function store($key, $value)
    {
        $this->data[$key] = $value;

        $sql = 'UPDATE @PREFIX@sessions SET data = {0} WHERE id = {1} LIMIT 1';
        $this->app['db']->query($sql, [serialize($this->data), $this->id]);
    }

    /**
     * Refreshes the session
     *
     * @return void
     */
    public function refresh()
    {
        $sql = 'UPDATE @PREFIX@sessions SET expire = {0} WHERE id = {1} LIMIT 1';
        $this->app['db']->query($sql, [$this->getExpire()->format('Y-m-d H:i:s'), $this->id]);

        // Update the assigned user's last activity time
        if ($user = $this->getUser())
            $user->setLastActive();
    }

    /**
     * Destoroys the session
     *
     * @return void
     */
    public function destroy()
    {
        $sql = 'DELETE FROM @PREFIX@sessions WHERE id = {0}';
        return $this->app['db']->query($sql, [$this->id]);

        $this->id = null;
        $this->user = null;
        $this->data = array();
        $this->lifetime = 0;
    }

    /**
     * Returns whether the session is active
     *
     * @return bool
     */
    public function isActive()
    {
        return isset($this->id);
    }
}
