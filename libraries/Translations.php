<?php
/**
 * Infernum
 * Copyright (C) 2015 IceFlame.net
 *
 * Permission to use, copy, modify, and/or distribute this software for
 * any purpose with or without fee is hereby granted, provided that the
 * above copyright notice and this permission notice appear in all copies.
 *
 * @package  FlameCore\Infernum
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  http://opensource.org/licenses/ISC ISC License
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
     * The language
     *
     * @var string
     */
    protected $language;

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
        $this->language = $language;

        // Load all strings of the selected language pack
        $this->strings = $app->cache('translations/'.$language, function () use ($app) {
            return $this->loadStrings($app);
        });
    }

    /**
     * Gets the translation of a string
     *
     * @param string $string The string to translate
     * @param array $vars Variables ('%var%') to replace as array. The key is the name of the variable.
     * @return string
     */
    public function get($string, array $vars = null)
    {
        // Check if a translation is available, if not use the input string
        if (isset($this->strings[$string])) {
            $translation = $this->strings[$string];
        } else {
            $translation = $string;
        }

        // Replace variables if needed
        if ($vars !== null) {
            foreach ($vars as $key => $val) {
                $translation = str_replace('%'.$key.'%', $val, $translation);
            }
        }

        return $translation;
    }

    /**
     * Loads the array of strings with their translations.
     *
     * @param \FlameCore\Infernum\Application $app The application context
     * @return array
     */
    protected function loadStrings(Application $app)
    {
        $sql = 'SELECT string, translation FROM <PREFIX>translations WHERE language = ?';
        $result = $app['db']->query($sql, [$this->language]);

        $strings = array();
        while ($entry = $result->fetch()) {
            $strings[$entry['string']] = $entry['translation'];
        }

        return $strings;
    }
}
