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
     * The data of the current language
     * @var      array
     * @access   private
     * @static
     */
    private static $_language_data;
    
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
        $languages = self::getAvailableLanguages();
        $default_lang = (string) ww_setting('main:lang');

        if (!in_array($default_lang, $languages))
            trigger_error('The default language is invalid or undefined', E_USER_ERROR);
        
        // Detect the user's preferred language
        if ($session_lang = SessionManager::read('language')) {
            // There was found a language setting in the user's session
            $detected_lang = $session_lang;
        } elseif ($browser_lang = self::findBestBrowserLanguage($languages)) {
            // Try to find out the language using browser information
            $detected_lang = $browser_lang;
        }

        // If the preferred language is not supported, fall back to the default language
        if (in_array($detected_lang, $languages)) {
            $language = $detected_lang;
        } else {
            $language = $default_lang;
        }
        
        $cache = new Cache('languages/'.$language);
        $language_data = $cache->data(function () use ($language) {
            $sql = 'SELECT * FROM @PREFIX@languages WHERE id = {0}';
            return System::db()->query($sql, [$language])->fetchAssoc();
        });
        
        self::$_language = $language;
        self::$_language_data = $language_data;
        
        setlocale(LC_ALL, explode(',', $language_data['locales']));

        self::$_t = new Translations($language);
    }
    
    /**
     * Returns the name of the currently used language pack
     * @return   string
     * @access   public
     * @static
     */
    public static function getCurrentLanguage() {
        if (!isset(self::$_language))
            trigger_error('The I18n system is not yet initialized', E_USER_ERROR);
        
        return self::$_language;
    }

    /**
     * Returns the text direction of the language
     * @return   string
     * @access   public
     * @static
     */
    public static function getTextDirection() {
        if (!isset(self::$_language_data))
            trigger_error('The I18n system is not yet initialized', E_USER_ERROR);
        
        return self::$_language_data['direction'];
    }
    
    /**
     * Returns a list of available language packs
     * @return   array
     * @access   public
     * @static
     */
    public static function getAvailableLanguages() {
        $cache = new Cache('languages/list');
        return $cache->data(function () {
            return System::db()->select('@PREFIX@languages', 'id')->fetchColumn();
        });
    }
    
    /**
     * Tries to find an available language that can best satisfy the browser languages list
     * @param    array    $supported_langs   The list of supported languages
     * @return   string
     * @access   public
     * @static
     */
    public static function findBestBrowserLanguage($supported_langs) {
        $browser_langs = Http::getBrowserLanguages();
        
        foreach ($browser_langs as $browser_lang) {
            if (in_array($browser_lang, $supported_langs))
                return $browser_lang;
        }
        
        return false;
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
