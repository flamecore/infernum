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
 * Class for evaluating cache files
 *
 * @author  Christian Neff <christian.neff@gmail.com>
 */
class Cache {

    /**
     * Does the cache file exist and is active?
     * @var     bool
     * @access  readonly
     */
    private $active = false;

    /**
     * The name of the cache file
     * @var     string
     * @access  private
     */
    private $_fileName;

    /**
     * Getter for readonly properties
     * @return  mixed
     * @access  public
     */
    public function __get($varName) {
        if ($varName[0] != '_')
            return $this->$varName;
    }

    /**
     * The class constructor
     * @param   string  $cacheFile  The name of the cache file to load
     * @param   int     $lifeTime   Lifetime of the cache in seconds, 0 means infinite. Defaults to 0.
     * @return  void
     * @access  public
     */
    public function __construct($cacheFile, $lifeTime = 0) {
        // determine cache file name
        $fileName = WW_DIR_CACHE.'/'.$cacheFile.'.cache.php';
        $this->_fileName = $fileName;
        
        // caching on, file exists and has not expired?
        if (Settings::get('core', 'caching') && file_exists($fileName)) {
            $expired = $lifeTime > 0 && filemtime($fileName) + $lifeTime < time();
            if (!$expired) {
                $this->active = true;
            } else {
                // the cache file has expired, so delete it
                unlink($fileName);
            }
        }
    }

    /**
     * Reads all data from the cache file
     * @return  mixed
     * @access  public
     */
    public function read() {
        // active? return false if not
        if (!$this->active) {
            return false;
        }
        
        // read the cache file and return the unserialized data
        $serialized = substr(file_get_contents($this->_fileName), 14);
        return unserialize($serialized);
    }

    /**
     * Writes the given data to the cache file
     * @param   mixed   $data  The data to store to the cache file
     * @return  bool
     * @access  public
     */
    public function store($data) {
        // active? return false if not
        if (!$this->active) {
            return false;
        }
        
        // serialize data and store to cache file
        $contents = '<?php die() ?>'.serialize($data);
        return file_put_contents($this->_fileName, $contents);
    }
    
}
