<?php

namespace Framework\Router;

use Closure;

class RouteCollector implements RouteCollectorInterface
{
    /**
     * The collector route data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Define whether the route collection is cacheable.
     *
     * @var bool
     */
    protected $cacheable = true;

    /**
     * Current collector route prefix.
     *
     * @var string
     */
    protected $currentRoutePrefix = '';

    /**
     * Current routes middleware stack.
     *
     * @var array
     */
    protected $currentMiddlewareStack = [];

    /**
     * Count the number of collected routes.
     *
     * @var int
     */ 
    protected $routeCounter = 0;

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
    public function route($methods, string $route, $handler): RouteInterface
    {
        $route = new Route(
            array_map('strtoupper', (array) $methods), 
            $this->currentRoutePrefix . $route, 
            $handler
        );
        $route->middleware($this->currentMiddlewareStack);
        $route->setName(sprintf('r-%s', ++$this->routeCounter));

        $this->data[] = $route;

        return $route;
    }

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
    public function group(string $routePrefix, callable $callback, array $middleware = []): void
    {
        $prefixBackup = $this->currentRoutePrefix;
        $this->currentRoutePrefix .= $routePrefix;
        
        $middlewareStackBackup = $this->currentMiddlewareStack;
        $this->currentMiddlewareStack += $middleware;
        
        if ($callback instanceof Closure) {
            $callback = $callback->bindTo($this);
        }
        $callback($this);

        $this->currentRoutePrefix = $prefixBackup;
        $this->currentMiddlewareStack = $middlewareStackBackup;
    }

    /**
     * Adds a GET route to the collection.
     *
     * This is simply an alias of $this->route('GET', $route, $handler).
     *
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function get(string $route, $handler): RouteInterface
    {
        return $this->route('GET', $route, $handler);
    }

    /**
     * Adds a POST route to the collection.
     *
     * This is simply an alias of $this->route('POST', $route, $handler).
     *
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function post(string $route, $handler): RouteInterface
    {
        return $this->route('POST', $route, $handler);
    }

    /**
     * Adds a PUT route to the collection.
     *
     * This is simply an alias of $this->route('PUT', $route, $handler).
     *
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function put(string $route, $handler): RouteInterface
    {
        return $this->route('PUT', $route, $handler);
    }

    /**
     * Adds a DELETE route to the collection.
     *
     * This is simply an alias of $this->route('DELETE', $route, $handler).
     *
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function delete(string $route, $handler): RouteInterface
    {
        return $this->route('DELETE', $route, $handler);
    }

    /**
     * Adds a PATCH route to the collection.
     *
     * This is simply an alias of $this->route('PATCH', $route, $handler).
     *
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function patch(string $route, $handler): RouteInterface
    {
        return $this->route('PATCH', $route, $handler);
    }

    /**
     * Adds a HEAD route to the collection.
     *
     * This is simply an alias of $this->route('HEAD', $route, $handler).
     *
     * @param string $route
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function head(string $route, $handler): RouteInterface
    {
        return $this->route('HEAD', $route, $handler);
    }

    /**
     * Adds an OPTIONS route to the collection.
     *
     * This is simply an alias of $this->route('OPTIONS', $pattern, $handler).
     *
     * @param string $pattern
     * @param mixed  $handler
     * @return RouteInterface
     */
    public function options(string $pattern, $handler): RouteInterface
    {
        return $this->route('OPTIONS', $pattern, $handler);
    }

    /**
     * Adds a route to all allowed HTTP methods to the collection.
     *
     * @param string $route
     * @param mixed $handler
     * @return RouteInterface
     */
    public function all(string $route, $handler): RouteInterface
    {
        return $this->route([
            'GET', 'POST', 'PUT', 'DELETE', 
            'PATCH', 'HEAD', 'OPTIONS'
        ], $route, $handler);
    }

    /**
     * Get the route collection data stored.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Find a route by its name in the collection.
     *
     * @param string $routeId
     * @return RouteInterface
     */
    public function getRoute(string $routeName): RouteInterface
    {
        foreach ($this->data as $route) {
            if ($routeName == $route->getName()) {
                return $route;
            }
        }
        throw new \LogicException(sprintf(
            'Cannot find route named [%s].', $routeName)
        );
    }

    /**
     * Check whether the route data defined in collector can be cached.
     *
     * @return bool
     */
    public function isCacheable(): bool
    {
        return $this->cacheable;
    }
}
