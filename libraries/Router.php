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

use FlameCore\Infernum\Exception\RouteNotFoundException;

/**
 * The Router class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Router
{
    /**
     * The kernel
     *
     * @var Kernel
     */
    private $kernel;

    /**
     * List of mounted modules
     *
     * @var array
     */
    private $modules = array();

    /**
     * Creates a Router object.
     *
     * @param Kernel $kernel The kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Parses the query string parameters and returns corresponding module, action and arguments.
     *
     * @param string $path The requested page path
     * @return array|bool Returns an array in the form `[module, action, arguments]` if the path
     *   matches a mountpoint, or FALSE if the path is empty.
     * @throws \FlameCore\Infernum\Exception\Router\RouteNotFoundException if the path is not empty
     *   and doesn't match any mountpoint.
     */
    public function parse($path)
    {
        if (!empty($path)) {
            $path_parts = explode('/', $path);

            if (count($path_parts) > 2) {
                $mount = array_shift($path_parts);
                $action = array_shift($path_parts);
                $arguments = $path_parts;
            } elseif (count($path_parts) == 2) {
                $mount = $path_parts[0];
                $action = $path_parts[1];
                $arguments = null;
            } else {
                $mount = $path_parts[0];
                $action = 'index';
                $arguments = null;
            }

            if (!$module = $this->getMountedModule($mount))
                throw new RouteNotFoundException(sprintf('No module mounted as "%s".', $mount));
        } else {
            return false;
        }

        return array($module, $action, $arguments);
    }

    /**
     * Gets defined mountpoints. If the $module parameter is set, only the mountpoints of this module are returned.
     *
     * @param string $module Name of the module (optional)
     * @return array Returns an array of mountpoints.
     */
    public function getMountpoints($module = null)
    {
        return array_keys($this->modules, $module);
    }

    /**
     * Gets the name of the module that is mounted on the given mountpoint.
     *
     * @param string $mountpoint The mountpoint
     * @return string Returns the name of the module.
     */
    public function getMountedModule($mountpoint)
    {
        return isset($this->modules[$mountpoint]) ? $this->modules[$mountpoint] : null;
    }

    /**
     * Mounts the given module.
     *
     * @param string $name The name of the module to mount
     * @param string $mountpoint Mountpoint alias
     */
    public function mountModule($name, $mountpoint)
    {
        if (!$this->kernel->moduleExists($name))
            throw new \LogicException(sprintf('Cannot mount module "%s" since it does not exist.', $name));

        $this->modules[$mountpoint] = $name;
    }

    /**
     * Unmounts the given module.
     *
     * @param string $name The name of the module to unmount
     */
    public function unmountModule($name)
    {
        $mountpoints = $this->getMountpoints($name);
        foreach ($mountpoints as $mountpoint) {
            unset($this->modules[$mountpoint]);
        }
    }
}
