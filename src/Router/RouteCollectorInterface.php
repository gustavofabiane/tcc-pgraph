<?php

namespace Framework\Router;

/**
 * Adapter for FastRoute's RouteCollector to work with Request Handlers.
 */
interface RouteCollectorInterface
{
    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function route($method, string $route, $handler): RouteInterface;

    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the passed callback will have 
     * the given group prefix prepended.
     *
     * @param string $routePrefix
     * @param callable $callback
     * @return void
     */
    public function group(string $routePrefix, callable $callback, array $middleware = []);

    /**
     * Adds a GET route to the collection.
     *
     * This is simply an alias of $this->route('GET', $route, $handler).
     *
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function get(string $route, $handler): RouteInterface;

    /**
     * Adds a POST route to the collection.
     *
     * This is simply an alias of $this->route('POST', $route, $handler).
     *
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function post(string $route, $handler): RouteInterface;

    /**
     * Adds a PUT route to the collection.
     *
     * This is simply an alias of $this->route('PUT', $route, $handler).
     *
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function put(string $route, $handler): RouteInterface;

    /**
     * Adds a DELETE route to the collection.
     *
     * This is simply an alias of $this->route('DELETE', $route, $handler).
     *
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function delete(string $route, $handler): RouteInterface;

    /**
     * Adds a PATCH route to the collection.
     *
     * This is simply an alias of $this->route('PATCH', $route, $handler).
     *
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function patch(string $route, $handler): RouteInterface;

    /**
     * Adds a HEAD route to the collection.
     *
     * This is simply an alias of $this->route('HEAD', $route, $handler).
     *
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function head(string $route, $handler): RouteInterface;

    /**
     * Adds a route to all allowed HTTP methods to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouteInterface
     */
    public function all(string $route, $handler): RouteInterface;

    /**
     * Get the route collection data stored.
     *
     * @return array
     */
    public function getData(): array;

    /**
     * Find a given route by its name in the collection.
     *
     * @param string $routeId
     * @return RouteInterface
     * 
     * @throws \LogicException if the given route name cannot be found.
     */
    public function getRoute(string $routeName): RouteInterface;

    /**
     * Check whether the route data defined in collector can be cached.
     *
     * @return bool
     */
    public function isCacheable(): bool;
}
