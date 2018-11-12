<?php

namespace Framework\Router;

use Psr\Http\Server\RequestHandlerInterface;

/**
 * Handle a request using a route callback wrapped by its middleware.
 */
interface RouteRequestHandlerInterface extends RequestHandlerInterface
{
    /**
     * Set the request handler route instance.
     *
     * @param RouteInterface $route
     * @return RouteRequestHandlerInterface
     * @throws \LogicException if the given route cannot handle requests.
     */
    public function route(RouteInterface $route): RouteRequestHandlerInterface;
}