<?php
/**
 * Webwork
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
 * @package     Webwork
 * @version     0.1-dev
 * @link        http://www.iceflame.net
 * @license     ISC License (http://www.opensource.org/licenses/ISC)
 */

/**
 * Class for reading and storing cache instances
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Cache {

    /**
     * The name of the cache instance
     * @var      string
     * @access   private
     */
    private $name;

    /**
     * The lifetime of the cache instance in seconds
     * @var      int
     * @access   private
     */
    private $lifetime;

    /**
     * Constructor
     * @param    string   $name       The name of the cache instance
     * @param    int      $lifetime   The lifetime of the cache instance in seconds (0 = infinite)
     * @return   void
     * @access   public
     */
    public function __construct($name, $lifetime = null) {
        if (!is_dir(WW_CACHE_PATH.'/data'))
            mkdir(WW_CACHE_PATH.'/data', 0777, true);

        if (!preg_match('#^[\w-+@\./]+$#', $name))
            trigger_error('Invalid cache name given ("'.$name.'")', E_USER_ERROR);

        if (!isset($lifetime))
            $lifetime = ww_config('cache_lifetime', 86400);

        $this->name = $name;
        $this->lifetime = (int) $lifetime;
    }

    /**
     * Reads data from cache. The $callback is used to generate the data if missing or expired.
     * @param    callable   $callback   The callback function that returns the data to store
     * @return   mixed
     * @access   public
     */
    public function data($callback) {
        if (!is_callable($callback)) {
            trigger_error('Invalid callback given for cache instance "'.$this->name.'"', E_USER_WARNING);
            return;
        }

        if (ww_config('enable_caching') == true) {
            // Caching is enabled, so we use a file
            $filename = WW_CACHE_PATH.'/data/'.$this->name.'.dat';

            // Check if the file exists
            if (file_exists($filename)) {
                $file_content = file_get_contents($filename);
                list($modified, $raw_data) = explode("\n", $file_content, 2);

                // Check if the file has expired. If so, there is no data we could use
                if ($this->lifetime > 0 && $modified + $this->lifetime < time())
                    $raw_data = null;
            }

            if (isset($raw_data)) {
                // We were able to retrieve data from the file
                return unserialize($raw_data);
            } else {
                // No data from file, so we use the data callback and store the given value
                $data = $callback();

                $file_content = time()."\n".serialize($data);
                file_put_contents($filename, $file_content);

                return $data;
            }
        } else {
            // Caching is disabled, so we use the data callback
            return $callback();
        }
    }

}
