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
     * The list of available language packs
     * @var      array
	 * @access   public
	 * @static
     */
    public static $languages;
    
    /**
     * The currently used language pack
     * @var      string
	 * @access   public
	 * @static
     */
    public static $language;
    
    /**
     * Translation engine object
     * @var      Translations
	 * @access   public
	 * @static
     */
    public static $t;

    /**
     * Initializes the internationalization system
     * @return   void
     * @access   public
     * @static
     */
    public static function init() {
        // Fetch list of available language packs
		self::$languages = self::getAvailableLanguages();

        // Detect the user's preferred language
        if (isset(Session::$data['language'])) {
            // There was found a language setting in the user's session
            self::$language = Session::$data['language'];
        } elseif ($browserLangs = Http::getAcceptLanguage()) {
            // We can use the browser language: Try to find the best match
            foreach (array_keys($browserLangs) as $browserLang) {
                if (isset(self::$languages[$browserLang])) {
                    self::$language = $browserLang;
                    break;
                }
            }
        }

        // If no preferred language was detected, fall back to the default language
        if (!isset(self::$language))
            self::$language = System::$settings['core']['lang'];

        setlocale(LC_ALL, self::$languages[self::$language]['locales']);

        self::$t = new Translations(self::$language);
    }
	
    /**
     * Returns a list of available language packs
     * @return   array
     * @access   public
     * @static
     */
	public static function getAvailableLanguages() {
        return get_cached('languages', function() {
            $result = System::$db->select('@PREFIX@languages');

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
    
}