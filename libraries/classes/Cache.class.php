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
 * Class for reading and storing cache files
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Cache {

    /**
     * Reads all data from the cache file. Returns the stored data on success. If the cache has expired, if caching is
     *   disabled or on failure, it returns NULL.
     * @param    string   $fileName   The name of the cache file
     * @return   mixed
     * @access   public
     * @static
     */
    public static function read($fileName) {
        if (!defined('WW_ENABLE_CACHING') || !WW_ENABLE_CACHING)
            return;

        $filePath = WW_ENGINE_PATH.'/cache/'.$fileName.'.cache';
        
        if (!file_exists($filePath))
            return;
        
        $fileContent = file_get_contents($filePath);
        list($modifiedTime, $data) = explode(',', $fileContent, 2);

        // Check if the file is fresh (has not yet expired)
        if ($lifeTime > 0 && $modifiedTime + $lifeTime < time())
            return;
        
        return unserialize($data);
    }
    
    /**
     * Writes the given data to the cache file. Returns the number of bytes that were written to the file or FALSE
     *   on failure.
     * @param    string   $fileName   The name of the cache file
     * @param    mixed    $data       The data to store to the cache file
     * @return   int
     * @access   public
     * @static
     */
    public static function store($fileName, $data) {
        if (!defined('WW_ENABLE_CACHING') || !WW_ENABLE_CACHING)
            return;

        $filePath = WW_ENGINE_PATH.'/cache/'.$fileName.'.cache';
        $fileContent = time().','.serialize($data);
        return file_put_contents($filePath, $fileContent);
    }
    
    /**
     * Deletes a given cache file. Returns TRUE on success and FALSE on failure.
     * @param    string   $fileName   The name of the cache file
     * @return   bool
     * @access   public
     * @static
     */
    public static function flush($fileName) {
        $filePath = WW_ENGINE_PATH.'/cache/'.$fileName.'.cache';
        return unlink($filePath);
    }
    
}
