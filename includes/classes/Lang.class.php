<?php
/**
 * HadesLite
 * Copyright (C) 2011 Hades Project
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
 * @package     HadesLite
 * @version     0.1-dev
 * @link        http://hades.iceflame.net
 * @license     ISC License (http://www.opensource.org/licenses/ISC)
 */

/**
 * Handling of different languages (with caching)
 *
 * @author  Martin Lantzsch <martin@linux-doku.de>
 */
class Lang {

    /**
     * The currently selected language
     * @var     string
     * @access  private
     * @static
     */
    private static $_lang;

    /**
     * All registered strings with their translation
     * @var     array
     * @access  private
     * @static
     */
    private static $_strings = array();

    /**
     * Initializes the language system and loads all strings (from database or from cache if available)
     * @param   string  $lang  The name of the language pack where the strings come from
     * @access  public
     * @static
     */
    public static function init($lang) {
        // set current language
        self::$_lang = $lang;
        
        // use the cache?
        $cache = new Cache('lang-'.self::$_lang);
        if ($cache->active) {
            // load all strings from the cache file
            self::$_strings = $cache->read();
        } else {
            // load all strings from the database
            $sql = 'SELECT s.string, s.translated FROM #PREFIX#lang_strings s, #PREFIX#lang_packs p'
                 . ' WHERE p.id = s.pack AND p.isocode = {0}';
            $result = Core::$db->query($sql, array($lang));
            while ($entry = $result->fetchAssoc())
                self::$_strings[$entry['string']] = $entry['translated'];
            
            // write to cache if enabled
            $cache->store(self::$_strings);
        }
    }

    /**
     * Gets the translation of a string
     * @param   string  $string  The string to translate
     * @param   array   $vars    Variables ('%var%') to replace as array. The key is the name of the variable
     *                             (without the percent signs).
     * @return  string
     * @access  public
     * @static
     */
    public static function get($string, $vars = null) {
        // check if a translation is available, if not use the input string
        if (isSet(self::$_strings[$string])) {
            $translated = self::$_strings[$string];
        } else {
            $translated = $string;
        }
        
        // replace variables if needed
        if (is_array($vars)) {
            foreach ($vars as $key => $val)
                $translated = str_replace('%'.$key.'%', $val, $translated);
        }
        
        // return translation
        return $translated;
    }

}
