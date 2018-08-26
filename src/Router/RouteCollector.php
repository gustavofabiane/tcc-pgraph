<?php

namespace Framework\Router;

use function Framework\isImplementerOf;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Container\ServiceResolverInterface;
use FastRoute\RouteCollector as FastRouteCollector;
use Framework\Router\RouteRequestHandler;

/**
 * Adapter for FastRoute's RouteCollector to work with Request Handlers.
 */
class RouteCollector extends FastRouteCollector
{
    /**
     * The service resolver instance.
     *
     * @var ServiceResolverInterface
     */
    protected $resolver;

    /**
     * Route group middleware
     *
     * @var array
     */
    protected $currentGroupMiddleware = [];

    /**
     * Creates a new RouteCollector instance.
     *
     * @param ServiceResolverInterface $resolver
     * @param RouteParser $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(
        ServiceResolverInterface $resolver,
        RouteParser $routeParser,
        DataGenerator $dataGenerator
    ) {
        parent::__construct($routeParser, $dataGenerator);
        $this->resolver = $resolver;
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string $route
     * @param mixed  $handler
     */
    public function addRoute($httpMethod, $route, $handler)
    {
        $handler = new RouteRequestHandler($resolvable, $this->resolver);
        $handler->middleware($this->currentGroupMiddleware);

        $route = $this->currentGroupPrefix . $route;
        $routeDatas = $this->routeParser->parse($route);
        foreach ((array) $httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, $handler);
            }
        }

        return $handler;
    }

    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the passed callback will have the given group prefix prepended.
     *
     * @param string $prefix
     * @param callable $callback
     */
    public function addGroup($prefix, callable $callback, array $groupMiddleware = [])
    {
        $previousGroupMiddleware = $this->currentGroupMiddleware;
        $this->currentGroupMiddleware = array_merge($previousGroupMiddleware, $groupMiddleware);
        
        parent::addGroup($prefix, $callback);
        
        $this->currentGroupMiddleware = $previousGroupMiddleware;
    }

    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the passed callback will have the given group prefix prepended.
     * 
     * Alias for RouteCollector::addGroup
     *
     * @param string $routePrefix
     * @param callable $callback
     */
    public function prefix($routePrefix, callable $callback, array $middleware = [])
    {
        parent::addGroup($routePrefix, $callback, $middleware);
    }

    /**
     * Adds a GET route to the collection
     *
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     * @return RequestHandlerInterface
     */
    public function get($route, $handler)
    {
        return $this->addRoute('GET', $route, $handler);
    }

    /**
     * Adds a POST route to the collection
     *
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     * @return RequestHandlerInterface
     */
    public function post($route, $handler)
    {
        return $this->addRoute('POST', $route, $handler);
    }

    /**
     * Adds a PUT route to the collection
     *
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     * @return RequestHandlerInterface
     */
    public function put($route, $handler)
    {
        return $this->addRoute('PUT', $route, $handler);
    }

    /**
     * Adds a DELETE route to the collection
     *
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     * @return RequestHandlerInterface
     */
    public function delete($route, $handler)
    {
        return $this->addRoute('DELETE', $route, $handler);
    }

    /**
     * Adds a PATCH route to the collection
     *
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     * @return RequestHandlerInterface
     */
    public function patch($route, $handler)
    {
        return $this->addRoute('PATCH', $route, $handler);
    }

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param string $route
     * @param mixed  $handler
     */
    public function head($route, $handler)
    {
        return $this->addRoute('HEAD', $route, $handler);
    }

    /**
     * Adds a route to all HTTP methods to the collection
     *
     * @param string $route
     * @param mixed $handler
     * @return RequestHandlerInterface
     */
    public function all($route, $handler)
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'HEAD', 'DELETE'];
        return $this->addRoute($methods, $route, $handler);
    }
}
