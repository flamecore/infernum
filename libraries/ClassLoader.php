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

/**
 * Loader for classes
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class ClassLoader
{
    /**
     * List of source paths for namespace prefixes
     *
     * @var array
     */
    protected $sources = array();

    /**
     * Creates a ClassLoader instance.
     *
     * @param string $prefix The primary namespace prefix
     * @param string $path The source path for the primary namespace
     */
    public function __construct($prefix, $path)
    {
        $this->addSource($prefix, $path);
    }

    /**
     * Registers the ClassLoader as autoloader.
     *
     * @return void
     */
    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Checks whether a source path for the given namespace is defined.
     *
     * @param string $prefix The namespace prefix
     * @return bool Returns TRUE if a source path for the given namespace is defined for the given namespace.
     */
    public function hasSource($prefix)
    {
        $prefix = $this->normalize($prefix);
        return isset($this->sources[$prefix]);
    }

    /**
     * Gets the source path for the given namespace.
     *
     * @param string $prefix The namespace prefix
     * @return string Returns the source path of the namespace or NULL if no source is defined for the namespace.
     */
    public function getSource($prefix)
    {
        $prefix = $this->normalize($prefix);
        return isset($this->sources[$prefix]) ? $this->sources[$prefix] : null;
    }

    /**
     * Defines the source path for the given namespace.
     *
     * @param string $prefix The namespace prefix
     * @param string $path The source path
     * @param string $base Pattern of base path and filename (relative to source path).
     */
    public function addSource($prefix, $path, $base = 'libraries/*.php')
    {
        if ($this->hasSource($prefix)) {
            return;
        }

        if (!is_dir($path)) {
            throw new \DomainException(sprintf('The path "%s" does not exist.', $path));
        }

        if (strpos($base, '*') === false) {
            throw new \InvalidArgumentException(sprintf('The base path does not contain a filename wildcard.', $prefix));
        }

        $prefix = $this->normalize($prefix);
        $this->sources[$prefix] = $path.'/'.$base;
    }

    /**
     * Loads the given class.
     *
     * @param string $name Name of the class to load
     * @return bool Returns FALSE if the class could not be loaded, TRUE otherwise.
     */
    public function loadClass($name)
    {
        if ($classfile = $this->findFile($name)) {
            require_once $classfile;
            return true;
        }

        return false;
    }

    /**
     * Searches all source paths for a class file with given name.
     *
     * @param string $class Name of the class
     * @return string|bool Returns the full filename of the found file. If no file is found FALSE is returned.
     */
    public function findFile($class)
    {
        $class = $this->normalize($class);

        foreach ($this->sources as $prefix => $pattern) {
            if (strpos($class, $prefix) === 0) {
                $name_without_prefix = substr($class, strlen($prefix));
                $file = str_replace('*', str_replace('\\', '/', $name_without_prefix), $pattern);

                if (file_exists($file)) {
                    return $file;
                }
            }
        }

        return false;
    }

    /**
     * Normalizes the given name.
     *
     * @param string $name The name to normalize
     * @return string Returns the normalized name.
     */
    protected function normalize($name)
    {
        return trim($name, '\\');
    }
}
