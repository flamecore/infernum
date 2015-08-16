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

use FlameCore\Infernum\Configuration\ThemeMetadata;

/**
 * The Theme class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Theme
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $stylesheets = array();

    /**
     * @var array
     */
    private $javascripts = array();

    /**
     * @param string $name
     * @param \FlameCore\Infernum\Kernel $kernel
     */
    public function __construct($name, Kernel $kernel)
    {
        $name = (string) $name;
        $path = $kernel->getPath().'/themes/'.$name;

        if (!is_dir($path)) {
            throw new \LogicException(sprintf('Theme "%s" does not exist.', $name));
        }

        $this->name = $name;
        $this->path = $path;

        $metadata = $this->loadMetadata();
        $this->stylesheets = $metadata['stylesheets'];
        $this->javascripts = $metadata['javascripts'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getStylesheets()
    {
        return $this->stylesheets;
    }

    /**
     * @return array
     */
    public function getJavascripts()
    {
        return $this->javascripts;
    }

    /**
     * @return array
     */
    private function loadMetadata()
    {
        try {
            $config = new ThemeMetadata($this->path.'/theme.yml');
            return $config->load();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Unable to load theme metadata: %s', $e->getMessage()));
        }
    }
}
