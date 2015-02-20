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

use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;

/**
 * The Logger class
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Logger extends AbstractLogger
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * Initializes the Logger.
     *
     * @param string $name The name of the log channel
     * @param \FlameCore\Infernum\Kernel $kernel The kernel
     * @throws \InvalidArgumentException if the name is invalid.
     */
    public function __construct($name, Kernel $kernel)
    {
        $name = (string) $name;

        if ($name === '') {
            throw new \InvalidArgumentException('The log channel name must not be empty');
        }

        $this->name = $name;
        $this->filename = $kernel->getPath().'/logs/'.$name.'.log';

        $this->active = $kernel->config('enable_logging');
    }

    /**
     * Logs a message.
     *
     * @param string $severity The severity level
     * @param string $message The message
     * @param array $context The context values (optional)
     * @return bool
     */
    public function log($severity, $message, array $context = [])
    {
        if (!$this->active) {
            return true;
        }

        if ($severity == LogLevel::EMERGENCY) {
            $tag = 'EMERGENCY';
        } elseif ($severity == LogLevel::ALERT) {
            $tag = 'ALERT';
        } elseif ($severity == LogLevel::CRITICAL) {
            $tag = 'CRITICAL';
        } elseif ($severity == LogLevel::ERROR) {
            $tag = 'ERROR';
        } elseif ($severity == LogLevel::WARNING) {
            $tag = 'WARNING';
        } elseif ($severity == LogLevel::NOTICE) {
            $tag = 'NOTICE';
        } elseif ($severity == LogLevel::INFO) {
            $tag = 'INFO';
        } elseif ($severity == LogLevel::DEBUG) {
            $tag = 'DEBUG';
        } else {
            throw new InvalidArgumentException('Invalid log severity level given.');
        }

        $datetime = date('Y-m-d H:i:s');
        $message = $this->interpolate((string) $message, $context);

        $logtext = "$datetime [$tag] $message";

        return error_log($logtext.PHP_EOL, 3, $this->filename);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message The message
     * @param array $context The context values (optional)
     * @return string
     */
    protected function interpolate($message, array $context = [])
    {
        $replace = array();
        foreach ($context as $key => $value) {
            $replace['{'.$key.'}'] = $value;
        }

        return strtr($message, $replace);
    }
}
