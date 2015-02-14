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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Base module controller
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class Controller
{
    /**
     * The application context
     *
     * @var \FlameCore\Infernum\Application
     */
    private $context;

    /**
     * Generates a new Controller object.
     *
     * @param \FlameCore\Infernum\Application $context The application context
     * @param mixed $extra The extra options (optional)
     */
    final public function __construct(Application $context, $extra = null)
    {
        $this->context = $context;

        $this->initialize($context, $extra);
    }

    /**
     * Executes the action with given arguments.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The Request object
     * @param string $action The action name
     * @param array $arguments The arguments as array (optional)
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \UnexpectedValueException if the action does not provide a valid response result.
     */
    final public function run(Request $request, $action, array $arguments = null)
    {
        if (!$this->actionExists($action)) {
            return $this->errorNotFound();
        }

        $result = call_user_func([$this, 'action_'.$action], $arguments, $request, $this->context);

        if ($result instanceof Response) {
            return $result;
        } elseif ($result instanceof View) {
            return new Response($result->render());
        } elseif (is_string($result)) {
            return new Response($result);
        } else {
            throw new \UnexpectedValueException(sprintf('Action "%s" of Controller "%s" does not provide a valid response result', $action, get_class($this)));
        }
    }

    /**
     * Checks whether an action exists
     *
     * @param string $action The action name
     * @return bool
     */
    final public function actionExists($action)
    {
        return method_exists($this, 'action_'.$action);
    }

    /**
     * Initializes the controller.
     *
     * @param \FlameCore\Infernum\Application $context The application context
     * @param mixed $extra The extra options
     */
    protected function initialize(Application $context, $extra)
    {
    }

    /**
     * Generates a message response using the global 'message_body' template
     *
     * @param string $message The text of the message to show. In the template, this value can be retrieved via
     *   the `message` variable.
     * @param string $type The type of the message, should be either 'info', 'success', 'warning' or 'error'.
     *   In the template, this value can be retrieved via the `type` variable.
     * @param int $status The status code (Default: 200)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    final protected function message($message, $type = 'info', $status = 200)
    {
        $view = new View('@global/message_body', $this->context);
        $view->set('message', $message);
        $view->set('type', $type);

        return new Response($view, $status);
    }

    /**
     * Generates a '404 Not Found' error response using the global '404_body' template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    final protected function errorNotFound()
    {
        $view = new View('@global/404_body', $this->context);
        return new Response($view, 404);
    }

    /**
     * Generates a '403 Forbidden' error response using the global '403_body' template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    final protected function errorForbidden()
    {
        $view = new View('@global/403_body', $this->context);
        return new Response($view, 403);
    }

    /**
     * Generates a redirect response.
     *
     * @param string $url The URL to redirect to
     * @param int $status The status code (302 by default)
     * @param array $headers The headers (Location is always set to the given URL)
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    final protected function redirect($url, $status = 302, $headers = [])
    {
        return new RedirectResponse($url, $status, $headers);
    }
}
