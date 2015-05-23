<?php
/**
 * Infernum
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
 * @package  FlameCore\Infernum
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

namespace FlameCore\Infernum;

use FlameCore\Infernum\Resource\Localization;
use Symfony\Component\HttpFoundation\Request;

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
     * @var \FlameCore\Infernum\Resource\Localization
     */
    private $locale;

    /**
     * The translation engine object
     *
     * @var \FlameCore\Infernum\Translations
     */
    private $translations;

    /**
     * Initializes the internationalization system.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request
     * @param \FlameCore\Infernum\Application $app The application context
     * @return \FlameCore\Infernum\International Returns the new International object.
     */
    public static function init(Request $request, Application $app)
    {
        $locales = Localization::listAll($app['db']);
        $defaultLang = $app->setting('site.language');

        if (!in_array($defaultLang, $locales)) {
            throw new \DomainException('The default language is invalid or undefined.');
        }

        // Detect the user's preferred language
        $detectedLang = $defaultLang;
        if (isset($app['session']) && $sessionLang = $app['session']->read('language')) {
            // There was found a language setting in the user's session
            $detectedLang = $sessionLang;
        } elseif ($browserLang = $request->getPreferredLanguage($locales)) {
            // Try to find out the language using browser information
            $detectedLang = $browserLang;
        }

        // If the preferred language is not supported, fall back to the default language
        $localeName = in_array($detectedLang, $locales) ? $detectedLang : $defaultLang;
        $locale = new Localization($localeName, $app['db']);

        return new self($locale, $app);
    }

    /**
     * Generates a International object.
     *
     * @param \FlameCore\Infernum\Resource\Localization $locale The locale to use
     * @param \FlameCore\Infernum\Application $app The application context
     */
    public function __construct(Localization $locale, Application $app)
    {
        $localeName = $locale->getID();

        $this->locale = $locale;
        $this->translations = new Translations($localeName, $app);
    }

    /**
     * Returns the used locale
     *
     * @return \FlameCore\Infernum\Localization
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Formats a number with grouped thousands.
     *
     * @param float $number The number to be formatted
     * @param int $decimals Sets the number of decimal points (Default: 0)
     * @param bool $groupThousands Enable grouping of thousands (Default: FALSE)
     * @return string
     */
    public function formatNumber($number, $decimals = 0, $groupThousands = true)
    {
        $separators = $this->locale->getNumberSeparators();

        $decimalPoint = $separators['decimal'];
        $thousandSep = $groupThousands ? $separators['thousand'] : '';

        return Format::number($number, $decimals, $decimalPoint, $thousandSep);
    }

    /**
     * Formats a number as a monetary string.
     *
     * @param float $number The number to be formatted
     * @param string $currency The currency to use
     * @return string
     */
    public function formatMoney($number, $currency)
    {
        $format = $this->locale->getMoneyFormat();

        return Format::money($number, $currency, $format);
    }

    /**
     * Formats the given time/date to a time of day string.
     *
     * @param mixed $input Time/Date to be formatted. Can be UNIX timestamp, DateTime object or time/date string.
     *   When omitted, the current time is used.
     * @return string
     */
    public function formatTime($input = null)
    {
        $format = $this->locale->getTimeFormat();

        return Format::time($input, $format);
    }

    /**
     * Formats the given time/date to a date string.
     *
     * @param mixed $input Time/Date to be formatted. Can be UNIX timestamp, DateTime object or time/date string.
     *   When omitted, the current time is used.
     * @param int $length The date length (1 = short [default], 2 = medium, 3 = long)
     * @param bool $withTime Add time to string? (Default = FALSE)
     * @return string
     */
    public function formatDate($input = null, $length = 1, $withTime = false)
    {
        $format = $this->locale->getDateFormat($length);

        if ($withTime) {
            $format .= ', ' . $this->locale->getTimeFormat();
        }

        return Format::time($input, $format);
    }

    /**
     * Gets the translation of a string
     *
     * @param string $string The string to translate
     * @param array $vars Variables (`%var%`) to replace as array. The key is the name of the variable.
     * @return string
     */
    public function translate($string, $vars = null)
    {
        return $this->translations->get($string, $vars);
    }
}
