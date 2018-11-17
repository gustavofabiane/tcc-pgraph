<?php

namespace Pgraph\Router;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Defines the contract to routes
 */
interface RouterInterface
{
    /**
     * Matches the given request to a specified route.
     *
     * @param ServerRequestInterface $request
     * @return RouteInterface
     */
    public function match(ServerRequestInterface $request): RouteInterface;

    /**
     * Collect routes defined in the given callable
     *
     * @param callable $routeDefinitionCallback
     * @return void
     */
    public function collect(callable $routeDefinitionCallback);
}