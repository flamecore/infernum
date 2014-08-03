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
     * The currently used locale pack
     * @var      string
     * @access   private
     * @static
     */
    static private $locale;
    
    /**
     * The data of the current locale
     * @var      array
     * @access   private
     * @static
     */
    static private $localeData;

    /**
     * The translation engine object
     * @var      Translations
     * @access   private
     * @static
     */
    static private $translations;

    /**
     * Initializes the internationalization system
     * @return   void
     * @access   public
     * @static
     */
    static public function init() {
        $locales = Localization::getAvailable();
        $default_lang = (string) System::setting('I18n:Language');

        if (!in_array($default_lang, $locales))
            trigger_error('The default language is invalid or undefined', E_USER_ERROR);
        
        // Detect the user's preferred language
        if ($session_lang = SessionManager::read('language')) {
            // There was found a language setting in the user's session
            $detected_lang = $session_lang;
        } elseif ($browser_lang = self::findBestBrowserLanguage($locales)) {
            // Try to find out the language using browser information
            $detected_lang = $browser_lang;
        }

        // If the preferred language is not supported, fall back to the default language
        if (in_array($detected_lang, $locales)) {
            $locale = $detected_lang;
        } else {
            $locale = $default_lang;
        }
        
        self::$locale = new Localization($locale);
        self::$translations = new Translations($locale);
    }
    
    /**
     * Returns the name of the currently used locale pack
     * @return   string
     * @access   public
     * @static
     */
    static public function getLocale() {
        if (!isset(self::$locale) || !(self::$locale instanceof Localization))
            trigger_error('The I18n system is not yet initialized', E_USER_ERROR);
        
        return self::$locale;
    }

    /**
     * Returns the translation engine object
     * @return   Translations
     * @access   public
     * @static
     */
    static public function translate($string, $vars = null) {
        if (!isset(self::$translations) || !(self::$translations instanceof Translations))
            trigger_error('The I18n system is not yet initialized', E_USER_ERROR);
        
        return self::$translations->get($string, $vars);
    }
    
    /**
     * Tries to find an available language that can best satisfy the browser languages list
     * @param    array    $supported_langs   The list of supported languages
     * @return   string
     * @access   private
     * @static
     */
    static private function findBestBrowserLanguage($supported_langs) {
        $browser_langs = Util::getBrowserLanguages();
        
        foreach ($browser_langs as $browser_lang) {
            if (in_array($browser_lang, $supported_langs))
                return $browser_lang;
        }
        
        return false;
    }
    
}
