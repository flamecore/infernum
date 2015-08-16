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
     * @param string $type The type of the message, must be either 'info', 'success', 'warning' or 'danger'. (Default: 'info')
     *   In the template, this value can be retrieved via the `type` variable.
     * @param int $status The status code (Default: 200)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    final protected function message($message, $type = 'info', $status = 200)
    {
        $view = new View('@global/message_body', $this->context);
        $view->set('message', $message);
        $view->set('type', $type);

        return new Response($view->render(), $status);
    }

    /**
     * Generates a '404 Not Found' error response using the global '404_body' template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    final protected function errorNotFound()
    {
        $view = new View('@global/404_body', $this->context);
        return new Response($view->render(), 404);
    }

    /**
     * Generates a '403 Forbidden' error response using the global '403_body' template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    final protected function errorForbidden()
    {
        $view = new View('@global/403_body', $this->context);
        return new Response($view->render(), 403);
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
