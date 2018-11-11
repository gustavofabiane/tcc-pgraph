<?php

namespace Framework\Router;

use FastRoute\Dispatcher;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Http\Handlers\HasMiddlewareTrait;

class Route implements RouteInterface
{
    /**
     * Available rouet status
     */
    const ROUTE_STATUS = [
        Dispatcher::FOUND, 
        Dispatcher::NOT_FOUND, 
        Dispatcher::METHOD_NOT_ALLOWED
    ];

    /**
     * The route name definition.
     *
     * @var string
     */
    protected $name;

    /**
     * The route allowed HTTP methods.
     *
     * @var array
     */
    protected $methods;
    
    /**
     * The route request handler.
     *
     * @var RouteRequestHandler
     */
    protected $handler;
    
    /**
     * The route defined pattern.
     *
     * @var string
     */
    protected $pattern;

    /**
     * The route path path
     *
     * @var string
     */
    protected $path;

    /**
     * The route status.
     *
     * @var int
     */
    protected $status;

    /**
     * The route arguments
     *
     * @var array
     */
    protected $arguments;

    /**
     * The route defined middleware
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Creates a new route instance.
     *
     * @param string $path
     * @param int $status
     * @param RouteRequestHandler|null $handler
     * @param array|null $arguments
     */
    public function __construct(
        array $methods = [],
        string $pattern = null, 
        $handler = null, 
        string $name = null
    ) {
        $this->methods = $methods;
        $this->pattern = $pattern;
        $this->handler = $handler;
        $this->name    = $name;
    }

    /**
     * Filter route status
     *
     * @param int $status
     * @return int
     * 
     * @throws \InvalidArgumentException if the status is invalid
     */
    private function filterStatus(int $status): int
    {
        if (in_array($status, static::ROUTE_STATUS)) {
            return $status;
        }

        throw new \InvalidArgumentException(
            sprintf('Route status %u is not valid', $status)
        );
    }

    /**
     * Set the route allowed HTTP methods.
     *
     * @param array|string $methods
     * @return RouteInterface
     */
    public function setMethods($methods): RouteInterface
    {
        $this->methods = (array) $methods;
        return $this;
    }

    /**
     * Get the route defined HTTP methods.
     *
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods ?: [];
    }
    
    /**
     * Set the route handler definition.
     *
     * @param mixed $handler
     * @return RouteInterface
     */
    public function setHandler($handler): RouteInterface
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * Get the route's request handler.
     *
     * @return RequestHandlerInterface|null
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set the route pattern.
     *
     * @param string $pattern
     * @return RouteInterface
     */
    public function setPattern(string $pattern): RouteInterface
    {
        $this->pattern = $pattern;
        return $this;
    }
    
    /**
     * Get the route definition pattern.
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Set the route matched path.
     *
     * @param string $path
     * @return string
     */
    public function setPath(string $path): RouteInterface
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get the route path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
    
    /**
     * Set the route matched arguments.
     *
     * @param array $arguments
     * @return RouteInterface
     */
    public function setArguments(array $arguments): RouteInterface
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * Get route arguments
     *
     * @param int $status
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments ?: [];
    }

    /**
     * Set the route status.
     *
     * @param integer $status
     * @return RouteInterface
     */
    public function setStatus(int $status): RouteInterface
    {
        if (!in_array($status, static::ROUTE_STATUS)) {
            throw new \InvalidArgumentException(
                sprintf('Route status %d is not valid', $status)
            );
        }
        
        $this->status = $status;
        return $this;
    }

    /**
     * Get the route defined status.
     *
     * @return integer
     */
    public function getStatus(): int
    {
        return $this->status ?: Dispatcher::NOT_FOUND;
    }

    /**
     * Checks whether the route is found.
     *
     * @return bool
     */
    public function isFound(): bool
    {
        return $this->status === Dispatcher::FOUND;
    }

    /**
     * Checks whether the route is not allowed
     *
     * @return bool
     */
    public function isNotAllowed(): bool
    {
        return $this->status === Dispatcher::METHOD_NOT_ALLOWED;
    }

    /**
     * Define route as named with the given route name.
     *
     * @param string $routeName
     * @return RouteInterface
     */
    public function setName(string $routeName): RouteInterface
    {
        $this->name = $routeName;
        return $this;
    }

    /**
     * Get the route name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?: '';
    }

    /**
     * Add a middleware definition to the route instance.
     *
     * @param callacle|\Psr\Http\Server\MiddlewareInterface $middleware
     * @return RouteInterface
     */
    public function add($middleware): RouteInterface
    {
        array_unshift($this->middleware, $middleware);
        return $this;
    }

    /**
     * Add a list of middleware definitions to the route instance.
     *
     * @param array $middlewareStack
     * @return RouteInterface
     */
    public function middleware(array $middlewareStack): RouteInterface
    {
        foreach ($middlewareStack as $middleware) {
            $this->add($middleware);
        }
        return $this;
    }

    /**
     * Get the route defined middleware stack.
     *
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}
