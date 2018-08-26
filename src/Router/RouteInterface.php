<?php

namespace Framework\Router;

use Psr\Http\Server\RequestHandlerInterface;

/**
 * A representation of the route matched from the router.
 */
interface RouteInterface
{
    /**
     * Get the request handler of the route.
     *
     * @return RequestHandlerInterface|callable
     */
    public function getHandler();

    /**
     * Get the route arguments
     *
     * @return array
     */
    public function getArguments(): array;

    /**
     * Checks whether the route is found.
     *
     * @return bool
     */
    public function found(): bool;

    /**
     * Checks whether the route is not allowed
     *
     * @return bool
     */
    public function notAllowed(): bool;
}
