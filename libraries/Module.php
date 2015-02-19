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
use FlameCore\Infernum\Configuration\ModuleMetadata;
use Symfony\Component\HttpFoundation\Request;

/**
 * The Module class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Module implements ExtensionAbstraction
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
     * @var string
     */
    private $namespace;

    /**
     * @var array
     */
    private $provides = array();

    /**
     * @var array
     */
    private $requires = array();

    /**
     * @var array
     */
    private $extra = array();

    /**
     * @var bool
     */
    private $run = false;

    /**
     * @param string $name The module name
     * @param \FlameCore\Infernum\Kernel $kernel The kernel
     * @param mixed $extra The extra options (optional)
     */
    public function __construct($name, Kernel $kernel, $extra = null)
    {
        if (!$kernel->moduleExists($name)) {
            throw new \LogicException(sprintf('Module "%s" does not exist.', $name));
        }

        $path = $kernel->getModulePath($name);

        if (!file_exists($path.'/controller.php')) {
            throw new \LogicException(sprintf('Module "%s" does not provide a controller.', $name));
        }

        $this->name = $name;
        $this->path = $path;
        $this->extra = $extra;

        $metadata = $this->loadMetadata();
        $this->namespace = $metadata['namespace'];
        $this->provides = $metadata['provides'];
        $this->requires = $metadata['requires'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function provides($type)
    {
        return $this->provides[$type];
    }

    /**
     * @return array
     */
    public function getRequiredPlugins()
    {
        return $this->requires['plugins'];
    }

    /**
     * @param \FlameCore\Infernum\Application $app
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $action
     * @param array $arguments
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function run(Application $app, Request $request, $action, $arguments)
    {
        if ($this->run) {
            throw new \LogicException(sprintf('Module "%s" is already run.', $this->name));
        }

        require_once $this->path.'/controller.php';

        $class = $this->namespace.'\Controller';

        if (!class_exists($class) || !is_subclass_of($class, __NAMESPACE__.'\Controller')) {
            throw new \LogicException(sprintf('Module "%s" does not provide a valid Controller class.', $this->name));
        }

        $this->run = true;

        $controller = new $class($app, $this->extra);
        return $controller->run($request, $action, $arguments);
    }

    /**
     * @return array
     */
    private function loadMetadata()
    {
        try {
            $config = new ModuleMetadata($this->path.'/module.yml');
            return $config->load();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Unable to load module "%s" metadata: %s', $this->name, $e->getMessage()));
        }
    }
}
