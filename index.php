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

use FlameCore\Infernum\Template\TemplateLoader;
use FlameCore\Infernum\Template\Twig\TwigEngine;
use Symfony\Component\HttpFoundation\Request;

/**
 * Infernum Core
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */

define('DS', DIRECTORY_SEPARATOR);
define('INFERNUM_PATH', DS != '/' ? str_replace(DS, '/', __DIR__) : __DIR__);

try {
    if (!is_readable(INFERNUM_PATH.'/vendor/autoload.php'))
        throw new \LogicException('Vendor autoloader not found or unreadable. Please make sure that you have installed the required libraries using Composer.');

    require_once INFERNUM_PATH.'/libraries/ClassLoader.php';
    require_once INFERNUM_PATH.'/vendor/autoload.php';

    $loader = new ClassLoader(__NAMESPACE__, INFERNUM_PATH);
    $loader->register();

    $request = Request::createFromGlobals();
    $domain = $request->server->get('SERVER_NAME');

    $kernel = new Kernel($domain, INFERNUM_PATH);
    $kernel['loader'] = $loader;

    $site = $kernel->boot($request->server);

    $app = new Application($site, $kernel);
    $app['session'] = Session::init($request, $app);
    $app['intl'] = International::init($request, $app);

    $loader = new TemplateLoader($app);
    $app['tpl'] = new TwigEngine($loader, $app);

    View::setTitle($app->setting('site.title'));

    $kernel->handle($request, $app);
} catch (\Exception $exception) {
    if (isset($kernel)) {
        $kernel->log($exception->getMessage(), 2);
        $verbosity = $kernel->config('enable_debugmode') ? $kernel->config('debug_verbosity', 1) : 0;
    } else {
        $verbosity = 1;
    }

    require INFERNUM_PATH.'/includes/errorpage.php';
    exit();
}
