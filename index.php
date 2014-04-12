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
 * Webwork Core
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */

define('DS', DIRECTORY_SEPARATOR);
define('WW_ENGINE_PATH', DS != '/' ? str_replace(DS, '/', __DIR__) : __DIR__);

require WW_ENGINE_PATH.'/includes/bootstrap.php';

try {
    SessionManager::init();
	International::init();

    Template::title(ww_setting('Main:SiteName'));

    @include WW_SITE_PATH.'/includes/global.php';
    
    // Split the path into its parts. Use frontpage path if no path is specified.
    $path = isset($_GET['p']) && $_GET['p'] != '' ? $_GET['p'] : ww_setting('Main:Frontpage');
    System::loadModuleFromPath($path);
} catch (Exception $error) {
    $tpl = new Template('error');
    if ($CONFIG['enable_debugmode'])
        $tpl->set('debug', $error->getMessage());
    $tpl->display();
}
