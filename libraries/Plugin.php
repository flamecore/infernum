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

use FlameCore\Infernum\Interfaces\ExtensionAbstraction;
use FlameCore\Infernum\Configuration\PluginMetadata;

/**
 * The Plugin class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Plugin implements ExtensionAbstraction
{
    private $name;

    private $namespace;

    private $path;

    private $provides = array();

    public function __construct($name, Kernel $kernel)
    {
        if (!$kernel->pluginExists($name))
            throw new \LogicException(sprintf('Plugin "%s" does not exist.', $name));

        $path = $kernel->getPluginPath($name);

        if (!file_exists($path.'/plugin.php'))
            throw new \LogicException(sprintf('Plugin "%s" does not provide an extension.', $name));

        $this->name = $name;
        $this->path = $path;

        $metadata = $this->loadMetadata();
        $this->namespace = $metadata['namespace'];
        $this->provides = $metadata['provides'];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function providesLibraries()
    {
        return $this->provides['libraries'];
    }

    public function initialize()
    {
        require_once $this->path.'/plugin.php';

        $class = $this->namespace.'\Extension';

        if (!class_exists($class) || !is_subclass_of($class, __NAMESPACE__.'\Extension'))
            throw new \RuntimeException(sprintf('Plugin "%s" does not provide a valid extension class.', $this->name));

        $class::initialize();
    }

    public function run(Application $app)
    {
        require_once $this->path.'/plugin.php';

        $class = $this->namespace.'\Extension';

        if (!class_exists($class) || !is_subclass_of($class, __NAMESPACE__.'\Extension'))
            throw new \RuntimeException(sprintf('Plugin "%s" does not provide a valid extension class.', $this->name));

        $class::run($app);
    }

    private function loadMetadata()
    {
        try {
            $config = new PluginMetadata($this->path.'/plugin.yml');
            return $config->load();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Unable to load plugin metadata: %s', $e->getMessage()));
        }
    }
}
