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

namespace FlameCore\Infernum\Resource;

/**
 * Object describing a locale.
 *
 * The identifier must be the ID of the locale.
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Localization extends AbstractResource
{
    /**
     * Returns the locale's ID
     *
     * @return int
     */
    public function getID()
    {
        return $this->get('id');
    }

    /**
     * Returns the name of the locale
     *
     * @return string
     */
    public function getName()
    {
        return $this->get('name');
    }

    /**
     * Returns the text direction of the locale
     *
     * @return string
     */
    public function getTextDirection()
    {
        return $this->get('text_direction');
    }

    /**
     * Returns the number separators of the locale
     *
     * @return array
     */
    public function getNumberSeparators()
    {
        return [
            'decimal'  => $this->get('number_sep_decimal'),
            'thousand' => $this->get('number_sep_thousand')
        ];
    }

    /**
     * Returns the money format of the locale
     *
     * @return string
     */
    public function getMoneyFormat()
    {
        return $this->get('fmt_money');
    }

    /**
     * Returns the time format of the locale
     *
     * @return string
     */
    public function getTimeFormat()
    {
        return $this->get('fmt_time');
    }

    /**
     * Returns the date format of the locale
     *
     * @param int $length The date length (1 = short [default], 2 = medium, 3 = long)
     * @return string
     */
    public function getDateFormat($length = 1)
    {
        if ($length >= 3) {
            return $this->get('fmt_date_long');
        } elseif ($length == 2) {
            return $this->get('fmt_date_medium');
        } else {
            return $this->get('fmt_date_short');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected static function getTable()
    {
        return 'locales';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getKeyName()
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getFields()
    {
        return array(
            'id' => 'string',
            'name' => 'string',
            'text_direction' => 'string',
            'number_sep_decimal' => 'string',
            'number_sep_thousand' => 'string',
            'fmt_money' => 'string',
            'fmt_time' => 'string',
            'fmt_date_short' => 'string',
            'fmt_date_medium' => 'string',
            'fmt_date_long' => 'string'
        );
    }
}
