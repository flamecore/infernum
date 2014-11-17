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

/**
 * Class for reading and storing cache instances
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Cache
{
    /**
     * The path to the cache directory
     *
     * @var string
     */
    private $path;

    /**
     * Creates a Cache object.
     *
     * @param string $path The path to the cache directory
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Reads data from the cache.
     *
     * @param string $name The name of the cache file
     * @return mixed
     */
    public function get($name)
    {
        if (!preg_match('#^[\w-+@\./]+$#', $name))
            throw new \InvalidArgumentException(sprintf('Given cache name "%s" is invalid.', $name));

        $filename = $this->getFilename($name);

        // Check if the file exists
        if (file_exists($filename)) {
            $file_content = file_get_contents($filename);
            list($expire, $raw_data) = explode("\n", $file_content, 2);

            // Check if the file is fresh
            if ($expire == 0 || $expire > time())
                return unserialize($raw_data);
        }

        return null;
    }

    /**
     * Writes data to the cache.
     *
     * @param string $name The name of the cache file
     * @param mixed $data The data to write
     * @param int $lifetime The lifetime of the cache file in seconds (0 = infinite)
     * @return bool
     */
    public function set($name, $data, $lifetime)
    {
        if (!preg_match('#^[\w-+@\./]+$#', $name))
            throw new \InvalidArgumentException(sprintf('Given cache name "%s" is invalid.', $name));

        $file_content = (time() + (int) $lifetime)."\n".serialize($data);
        return file_put_contents($this->getFilename($name), $file_content);
    }

    /**
     * Clears the cache.
     *
     * @return void
     */
    public function clear()
    {
        $iterator = new RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $filename => $file) {
            if ($file->isDir()) {
                rmdir($filename);
            } else {
                unlink($filename);
            }
        }
    }

    private function getFilename($name)
    {
        return $this->path.'/'.$name.'.dat';
    }
}
