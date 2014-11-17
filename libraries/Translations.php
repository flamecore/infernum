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

/**
 * Handling of different languages (with caching)
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Translations
{
    /**
     * All registered strings with their translation
     *
     * @var array
     */
    private $strings = array();

    /**
     * Constructor
     *
     * @param string $language The language
     * @param \FlameCore\Infernum\Application $app The application context
     */
    public function __construct($language, Application $app)
    {
        // Load all strings of the selected language pack
        $this->strings = $app->cache('translations/'.$language, function () use ($language, $app) {
            $sql = 'SELECT string, translation FROM @PREFIX@translations WHERE language = {0}';
            $result = $app['db']->query($sql, array($language));

            $strings = array();
            while ($entry = $result->fetch())
                $strings[$entry['string']] = $entry['translation'];

            return $strings;
        });
    }

    /**
     * Gets the translation of a string
     *
     * @param string $string The string to translate
     * @param array $vars Variables ('%var%') to replace as array. The key is the name of the variable.
     * @return string
     */
    public function get($string, $vars = null)
    {
        // Check if a translation is available, if not use the input string
        if (isset($this->strings[$string])) {
            $translation = $this->strings[$string];
        } else {
            $translation = $string;
        }

        // Replace variables if needed
        if (is_array($vars)) {
            foreach ($vars as $key => $val)
                $translation = str_replace('%'.$key.'%', $val, $translation);
        }

        return $translation;
    }
}
