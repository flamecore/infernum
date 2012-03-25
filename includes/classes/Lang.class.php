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
 * Handling of different languages (with caching)
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Lang {

    /**
     * The currently selected language pack
     * @var      string
     * @access   private
     * @static
     */
    private static $_langPack;

    /**
     * All registered strings with their translation
     * @var      array
     * @access   private
     * @static
     */
    private static $_strings = array();

    /**
     * Initializes the language system and loads all strings (from database or from cache if available)
     * @return   void
     * @access   public
     * @static
     */
    public static function init() {
        global $db;
        
        $userLang = self::getUserLang();
        if ($userLang !== false) {
            self::setLangPack($userLang);
        } else {
            self::setLangPack();
        }

        $cache = new Cache('langpack_'.self::$_langPack);
        if ($cache->active) {
            // load all strings from the cache file
            self::$_strings = $cache->read();
        } else {
            // load all strings of the selected language pack from the database
            $sql = 'SELECT string, translated FROM @PREFIX@lang_strings WHERE langpack = {0}';
            $result = $db->query($sql, array(self::$_langPack));
            while ($entry = $result->fetchAssoc())
                self::$_strings[$entry['string']] = $entry['translated'];

            // write to cache if enabled
            $cache->store(self::$_strings);
        }
    }
    
    /**
     * Gets the currently selected language pack
     * @return   string
     * @access   public
     * @static
     */
    public static function getLangPack() {
        return self::$_langPack;
    }

    /**
     * Sets the currently selected language pack. If the given language pack exists TRUE is returned, otherwise or if no
     *   pack is given the default language defined in the configuration will be used and FALSE is returned.
     * @param    string   $lang   The new language pack to use. If this parameter is omitted, the default language defined
     *                              in the configuration will be used.
     * @return   bool
     * @access   public
     * @static
     */
    public static function setLangPack($lang = null) {
        global $db;
        
        // fetch list of language packs
        $cache = new Cache('langpacks');
        if ($cache->active) {
            // from cache
            $langInfo = $cache->read();
        } else {
            // from database
            $sql = 'SELECT id, locale FROM @PREFIX@lang_packs';
            $result = $db->query($sql, array($lang));
            while ($data = $result->fetchAssoc())
                $langInfo[$data['id']] = explode(',', $data['locale']);
            
            // store to cache
            $cache->store($langInfo);
        }
        
        // does given language pack exist? if no, fall back to default language
        if (!isset($lang) || !array_key_exists($lang, $langInfo))
            $lang = Settings::get('core', 'lang');
        
        // update current language
        self::$_langPack = $lang;
        
        // update locale setting
        if (isset($langInfo[$lang]) && !empty($langInfo[$lang])) {
            setlocale(LC_ALL, $langInfo[$lang]);
        } else {
            return false;
        }
        
        return true;
    }
    
    /**
     * Returns the user's preferred language that is defined by the user language cookie. If the cookie is not set, it
     *   returns FALSE.
     * @return   string
     * @access   public
     * @static
     */
    public static function getUserLang() {
        return Http::getCookie('language');
    }

    /**
     * Sets the user language cookie that is used to define the user's preferred language. The cookie is automatically
     *   detected and the language pack to use is set accordingly. 
     * @param    string   $lang   The language to set
     * @return   bool
     * @access   public
     * @static
     */
    public static function setUserLang($lang) {
        return Http::setCookie('language', $lang, '+365d');
    }

    /**
     * Gets the translation of a string
     * @param    string   $string   The string to translate
     * @param    array    $vars     Variables ('%var%') to replace as array. The key is the name of the variable (without
     *                                the percent signs).
     * @return   string
     * @access   public
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
