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

namespace FlameCore\Infernum;

use FlameCore\Infernum\Exceptions\RouteNotFoundException;

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
     * The module options
     *
     * @var array
     */
    private $options = array();

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
     * @return array|bool Returns an array with module, action, arguments and options if the path
     *   matches a mountpoint, or FALSE if the path is invalid or empty.
     * @throws \FlameCore\Infernum\Exceptions\RouteNotFoundException if the path is not empty
     *   but does not match any mountpoint.
     */
    public function parse($path)
    {
        if (empty($path)) {
            return false;
        }

        $parts = explode('/', $path);

        if (count($parts) > 2) {
            $mount = array_shift($parts);
            $action = array_shift($parts);
            $arguments = $parts;
        } elseif (count($parts) == 2) {
            $mount = $parts[0];
            $action = $parts[1];
            $arguments = null;
        } else {
            $mount = $parts[0];
            $action = 'index';
            $arguments = null;
        }

        if (!$module = $this->getMountedModule($mount)) {
            throw new RouteNotFoundException(sprintf('No module mounted as "%s".', $mount));
        }

        return array(
            'module'    => $module,
            'action'    => $action,
            'arguments' => $arguments,
            'extra'     => $this->options[$mount]
        );
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
     * @param string $alias The mountpoint alias (optional)
     * @param mixed $extra The extra options of the module (optional)
     */
    public function mountModule($name, $alias = null, $extra = null)
    {
        if (!$this->kernel->moduleExists($name)) {
            throw new \LogicException(sprintf('Cannot mount module "%s" since it does not exist.', $name));
        }

        $alias = (string) $alias;

        if ($alias !== '') {
            $mountpoint = $alias;

            if (isset($this->modules[$mountpoint])) {
                throw new \LogicException(sprintf('Cannot mount module "%s" with alias "%s" since this mountpoint name is already in use.', $name, $alias));
            }
        } else {
            $nameparts = explode('/', $name);
            $mountpoint = array_pop($nameparts);

            if (isset($this->modules[$mountpoint])) {
                throw new \LogicException(sprintf('Cannot mount module "%s" as "%s" since this mountpoint name is already in use. Please make use of an alias.', $name, $mountpoint));
            }
        }

        $this->modules[$mountpoint] = $name;
        $this->options[$mountpoint] = $extra;
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
