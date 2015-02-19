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

use FlameCore\Infernum\Configuration\SiteConfiguration;
use FlameCore\Infernum\Configuration\SiteSettings;

/**
 * The Site class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Site
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
    private $routes;

    /**
     * @var array
     */
    private $plugins;

    /**
     * @param string $name
     * @param \FlameCore\Infernum\Kernel $kernel
     */
    public function __construct($name, Kernel $kernel)
    {
        $path = $kernel['path'].'/websites/'.$name;

        if (!is_dir($path)) {
            throw new \LogicException(sprintf('Directory of site "%s" does not exist (%s).', $name, $path));
        }

        $this->name = $name;
        $this->path = $path;

        $config = $this->loadConfiguration();
        $this->routes = $config['routes'];
        $this->plugins = $config['plugins'];
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
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * @return array
     */
    public function loadSettings()
    {
        try {
            $config = new SiteSettings($this->path.'/settings.yml');
            return $config->load();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Unable to load settings: %s', $e->getMessage()));
        }
    }

    /**
     * @return array
     */
    private function loadConfiguration()
    {
        try {
            $config = new SiteConfiguration($this->path.'/site.yml');
            return $config->load();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Unable to load site configuration: %s', $e->getMessage()));
        }
    }
}
