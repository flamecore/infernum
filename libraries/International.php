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
 * @package  FlameCore\Webwork
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

namespace FlameCore\Webwork;

/**
 * Simple internationalization system
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class International
{
    /**
     * The currently used locale pack
     *
     * @var \FlameCore\Webwork\Localization
     */
    private static $locale;

    /**
     * The translation engine object
     *
     * @var \FlameCore\Webwork\Translations
     */
    private static $translations;

    /**
     * Initializes the internationalization system
     *
     * @return void
     */
    public static function init()
    {
        $locales = Localization::getAvailable();
        $defaultLang = (string) System::setting('I18n:Language');

        if (!in_array($defaultLang, $locales))
            throw new \DomainException('The default language is invalid or undefined');

        // Detect the user's preferred language
        if ($sessionLang = System::getSession()->read('language')) {
            // There was found a language setting in the user's session
            $detectedLang = $sessionLang;
        } elseif ($browserLang = self::findBestBrowserLanguage($locales)) {
            // Try to find out the language using browser information
            $detectedLang = $browserLang;
        }

        // If the preferred language is not supported, fall back to the default language
        $locale = in_array($detectedLang, $locales) ? $detectedLang : $defaultLang;

        self::$locale = new Localization($locale);
        self::$translations = new Translations($locale);
    }

    /**
     * Returns the used locale
     *
     * @return \FlameCore\Webwork\Localization
     */
    public static function getLocale()
    {
        if (!isset(self::$locale) || !(self::$locale instanceof Localization))
            throw new \LogicException('The I18n system is not yet initialized');

        return self::$locale;
    }

    /**
     * Gets the translation of a string
     *
     * @param string $string The string to translate
     * @param array $vars Variables (`%var%`) to replace as array. The key is the name of the variable.
     * @return string
     */
    public static function translate($string, $vars = null)
    {
        if (!isset(self::$translations) || !(self::$translations instanceof Translations))
            throw new \LogicException('The I18n system is not yet initialized');

        return self::$translations->get($string, $vars);
    }

    /**
     * Tries to find an available language that can best satisfy the browser languages list
     *
     * @param array $supported The list of supported languages
     * @return string
     */
    private static function findBestBrowserLanguage($supported)
    {
        $languages = Util::getBrowserLanguages();

        foreach ($languages as $language) {
            if (in_array($language, $supported))
                return $language;
        }

        return false;
    }
}
