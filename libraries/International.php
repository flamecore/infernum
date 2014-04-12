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
    private static $_locale;
    
    /**
     * The data of the current locale
     * @var      array
     * @access   private
     * @static
     */
    private static $_locale_data;

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
        $locales = self::getAvailableLocales();
        $default_lang = (string) ww_setting('Main:Language');

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
        
        $cache = new Cache('locales/'.$locale);
        $locale_data = $cache->data(function () use ($locale) {
            $sql = 'SELECT * FROM @PREFIX@locales WHERE id = {0}';
            return System::db()->query($sql, [$locale])->fetchAssoc();
        });
        
        self::$_locale = $locale;
        self::$_locale_data = $locale_data;

        self::$_t = new Translations($locale);
    }
    
    /**
     * Returns the name of the currently used locale pack
     * @return   string
     * @access   public
     * @static
     */
    public static function getCurrentLocale() {
        if (!isset(self::$_locale))
            trigger_error('The I18n system is not yet initialized', E_USER_ERROR);
        
        return self::$_locale;
    }

    /**
     * Returns the text direction of the locale
     * @return   string
     * @access   public
     * @static
     */
    public static function getTextDirection() {
        if (!isset(self::$_locale_data))
            trigger_error('The I18n system is not yet initialized', E_USER_ERROR);
        
        return self::$_locale_data['text_direction'];
    }

    /**
     * Returns the number separators
     * @return   array
     * @access   public
     * @static
     */
    public static function getNumberSeparators() {
        if (!isset(self::$_locale_data))
            trigger_error('The I18n system is not yet initialized', E_USER_ERROR);
        
        return [
            'decimal'  => self::$_locale_data['number_sep_decimal'],
            'thousand' => self::$_locale_data['number_sep_thousand']
        ];
    }

    /**
     * Returns the money format
     * @return   string
     * @access   public
     * @static
     */
    public static function getMoneyFormat() {
        if (!isset(self::$_locale_data))
            trigger_error('The I18n system is not yet initialized', E_USER_ERROR);
        
        return self::$_locale_data['fmt_money'];
    }

    /**
     * Returns the time format
     * @return   string
     * @access   public
     * @static
     */
    public static function getTimeFormat() {
        if (!isset(self::$_locale_data))
            trigger_error('The I18n system is not yet initialized', E_USER_ERROR);
        
        return self::$_locale_data['fmt_time'];
    }

    /**
     * Returns the date format
     * @param    int      $length   The date length (1 = short [default], 2 = medium, 3 = long)
     * @return   string
     * @access   public
     * @static
     */
    public static function getDateFormat($length = 1) {
        if (!isset(self::$_locale_data))
            trigger_error('The I18n system is not yet initialized', E_USER_ERROR);
            
        if ($length >= 3) {
            return self::$_locale_data['fmt_date_long'];
        } elseif ($length == 2) {
            return self::$_locale_data['fmt_date_medium'];
        } else {
            return self::$_locale_data['fmt_date_short'];
        }
    }
    
    /**
     * Returns a list of available locale packs
     * @return   array
     * @access   public
     * @static
     */
    public static function getAvailableLocales() {
        $cache = new Cache('locales/list');
        return $cache->data(function () {
            return System::db()->select('@PREFIX@locales', 'id')->fetchColumn();
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
