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

namespace FlameCore\Infernum\Template;

use FlameCore\Infernum\Application;
use FlameCore\Infernum\Template\Exception\BadNameError;
use FlameCore\Infernum\Template\Exception\NotFoundError;
use Twig_LoaderInterface, Twig_ExistsLoaderInterface;

/**
 * Loader for template engines
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class TemplateLoader implements Twig_LoaderInterface, Twig_ExistsLoaderInterface
{
    private $app;

    private $namespaces = array();

    final public function __construct(Application $app)
    {
        $this->setNamespace('global', $app->getTemplatePath());

        $this->app = $app;
    }

    public function getSource($template)
    {
        return file_get_contents($this->locate($template));
    }

    public function getCacheKey($template)
    {
        return $this->locate($template);
    }

    public function isFresh($template, $time)
    {
        return filemtime($this->locate($template)) <= $time;
    }

    public function exists($template)
    {
        try {
            $this->locate($template);
            return true;
        } catch (NotFoundError $e) {
            return false;
        }
    }

    /**
     * Returns the local template path
     *
     * @return string|bool
     */
    final public function getLocalPath()
    {
        return $this->app->getTemplatePath(true);
    }

    /**
     * Checks whether or not the given namespace is defined
     *
     * @param string $namespace The namespace
     * @return bool
     */
    final public function isNamespaceDefined($namespace)
    {
        return isset($this->namespaces[$namespace]);
    }

    /**
     * Returns the template path of the given namespace
     *
     * @param string $namespace The namespace
     * @return string|bool
     */
    final public function getNamespace($namespace)
    {
        return isset($this->namespaces[$namespace]) ? $this->namespaces[$namespace] : false;
    }

    /**
     * Assigns a namespace with given template path
     *
     * @param string $namespace The namespace
     * @param string $path The template path of the namespace
     */
    final public function setNamespace($namespace, $path)
    {
        $this->namespaces[$namespace] = $path;
    }

    /**
     * Searches a template and returns its full filename
     *
     * @param string $template The name of the template
     * @return string
     * @throws Exception_Template_NotFoundError, Exception_Template_BadNameError
     */
    private function locate($template)
    {
        $template = preg_replace('#/{2,}#', '/', strtr((string) $template, '\\', '/'));

        if (strpos($template, "\0") !== false)
            throw new BadNameError('A template name cannot contain NUL bytes.');

        $template = ltrim($template, '/');
        $parts = explode('/', $template);
        $level = 0;
        foreach ($parts as $part) {
            if ($part === '..') {
                --$level;
            } elseif ($part !== '.') {
                ++$level;
            }

            if ($level < 0)
                throw new BadNameError(sprintf('Looks like you try to load a template outside configured directories. (%s)', $template));
        }

        if ($template[0] == '@') {
            if (false === $pos = strpos($template, '/'))
                throw new BadNameError(sprintf('Malformed namespaced template name "%s". (expecting "@namespace/template_name")', $template));

            $namespace = substr($template, 1, $pos - 1);

            if (!$this->isNamespaceDefined($namespace))
                throw new BadNameError(sprintf('Cannot find template "%s": The template namespace "%s" is not defined.', $template, $namespace));

            $name = substr($template, $pos + 1);
            $path = $this->getNamespace($namespace);

            $filename = "$path/$name.twig";
        } else {
            $localPath = $this->getLocalPath();

            if (!$localPath)
                throw new BadNameError(sprintf('Cannot find template "%s": There is no local template path defined.', $template));

            $filename = "$localPath/$template.twig";
        }

        if (!file_exists($filename))
            throw new NotFoundError(sprintf('Unable to find template "%s". (looked into: %s)', $template, $path));

        return $filename;
    }
}
