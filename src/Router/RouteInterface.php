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
     * Set the route status
     *
     * @param int $status
     * @return void
     */
    public function setStatus(int $status);

    /**
     * Set the route arguments
     *
     * @param array $arguments
     * @return void
     */
    public function setArguments(array $arguments);

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
