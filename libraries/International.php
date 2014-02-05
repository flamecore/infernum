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
 * Simple internationalization system
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class International {
    
    /**
     * The currently used language pack
     * @var      string
     * @access   private
     * @static
     */
    private static $_language;
    
    /**
     * The translation engine object
     * @var      Translations
     * @access   private
     * @static
     */
    private static $_t;

    /**
     * Initializes the internationalization system
     * @return   void
     * @access   public
     * @static
     */
    public static function init() {
        // Fetch list of available language packs
        $languages = self::getAvailableLanguages();

        // Detect the user's preferred language
        if ($language = SessionManager::read('language')) {
            // There was found a language setting in the user's session
            self::$_language = $language;
        } elseif ($browserLangs = Http::getAcceptLanguage()) {
            // We have to use the browser language: Try to find the best match
            foreach (array_keys($browserLangs) as $browserLang) {
                if (isset($languages[$browserLang])) {
                    self::$_language = $browserLang;
                    break;
                }
            }
        }

        // If no preferred language was detected, fall back to the default language
        if (!isset(self::$_language))
            self::$_language = ww_setting('main:lang');

        setlocale(LC_ALL, $languages[self::$_language]['locales']);

        self::$_t = new Translations(self::$_language);
    }
    
    /**
     * Returns the name of the currently used language pack
     * @return   string
     * @access   public
     * @static
     */
    public static function getLanguage() {
        if (!isset(self::$_language))
            trigger_error('The I18n system is not yet initialized', E_USER_ERROR);
        
        return self::$_language;
    }
    
    /**
     * Returns a list of available language packs
     * @return   array
     * @access   public
     * @static
     */
    public static function getAvailableLanguages() {
        $cache = new Cache('languages');
        return $cache->data(function () {
            $result = System::db()->select('@PREFIX@languages');

            $languages = array();
            while ($data = $result->fetchAssoc()) {
                $languages[$data['id']] = array(
                    'name'      => $data['name'],
                    'direction' => $data['direction'],
                    'locales'   => explode(',', $data['locales'])
                );
            }

            return $languages;
        });
    }
    
    /**
     * Returns the translation engine object
     * @return   Translations
     * @access   public
     * @static
     */
    public static function t() {
        if (!isset(self::$_t) || !(self::$_t instanceof Translations))
            trigger_error('The I18n system is not yet initialized', E_USER_ERROR);
        
        return self::$_t;
    }
    
}
