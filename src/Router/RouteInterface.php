<?php

namespace Framework\Router;

use FastRoute\Dispatcher;

/**
 * A representation of the route matched from the router.
 */
interface RouteInterface
{
    /**
     * Set the route allowed HTTP methods.
     *
     * @param array|string $methods
     * @return RouteInterface
     */
    public function setMethods($methods): RouteInterface;

    /**
     * Get the route defined HTTP methods.
     *
     * @return array
     */
    public function getMethods(): array;

    /**
     * Set the route handler definition.
     *
     * @param mixed $handler
     * @return RouteInterface
     */
    public function setHandler($handler): RouteInterface;

    /**
     * Get the request handler of the route.
     *
     * @return callable|Psr\Http\Server\RequestHandlerInterface
     */
    public function getHandler();

    /**
     * Set the route pattern.
     *
     * @param string $pattern
     * @return RouteInterface
     */
    public function setPattern(string $pattern): RouteInterface;
    
    /**
     * Get the route definition pattern.
     *
     * @return string
     */
    public function getPattern(): string;

    /**
     * Set the route matched path.
     *
     * @param string $path
     * @return string
     */
    public function setPath(string $path): RouteInterface;

    /**
     * Get the route matched path if exists.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Set the route matched arguments.
     *
     * @param array $arguments
     * @return RouteInterface
     */
    public function setArguments(array $arguments): RouteInterface;

    /**
     * Get the route arguments
     *
     * @return array
     */
    public function getArguments(): array;

    /**
     * Set the route status.
     *
     * @param integer $status
     * @return RouteInterface
     */
    public function setStatus(int $status): RouteInterface; 

    /**
     * Get the route defined status.
     *
     * @return integer
     */
    public function getStatus(): int;

    /**
     * Checks whether the route is found.
     *
     * @return bool
     */
    public function isFound(): bool;

    /**
     * Checks whether the route is not allowed
     *
     * @return bool
     */
    public function isNotAllowed(): bool;

    /**
     * Define route as named with the given route name.
     *
     * @param string $routeName
     * @return RouteInterface
     */
    public function named(string $routeName): RouteInterface;

    /**
     * Add a middleware definition to the route instance.
     *
     * @param callacle|\Psr\Http\Server\MiddlewareInterface $middleware
     * @return RouteInterface
     */
    public function add($middleware): RouteInterface;

    /**
     * Add a list of middleware definitions to the route instance.
     *
     * @param array $middlewareStack
     * @return RouteInterface
     */
    public function middleware(array $middlewareStack): RouteInterface;

    /**
     * Get the route defined middleware stack.
     *
     * @return array
     */
    public function getMiddleware(): array;
}
