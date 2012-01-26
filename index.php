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
 * @author  Christian Neff <christian.neff@gmail.com>
 */

try {
    // load bootstrap
    require_once 'includes/bootstrap.php';

    // set page title
    Template::setTitle(Settings::get('core', 'site_name'));

    // get module from params
    $path = new Path($_GET['p'], Settings::get('core', 'frontpage'));

    // load module if exists
    $module = $path->controller;
    $moduleFile = WW_DIR_MODULES.'/'.$module.'.php';
    
    if (!file_exists($moduleFile))
        throw new Exception('Module "'.$module.'" does not exist');
    
    include $moduleFile;
} catch (Exception $error) {
    echo '<strong>Error:</strong> '.$error->getMessage();
    exit();
}
