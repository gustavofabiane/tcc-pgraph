<?php

namespace Framework\Router;

use Closure;
use LogicException;
use function Framework\isImplementerOf;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Framework\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Handler for route callback execution
 */
class RouteRequestHandler implements RequestHandlerInterface
{
    /**
     * The route handled.
     *
     * @var RouteInterface
     */
    protected $route;

    /**
     * Route middleware stack
     *
     * @var array
     */
    protected $middleware;

    /**
     * The application container instance.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Create a new instance of RouteRequestHandler.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Handle the server request recieved and then
     * returns a response after middleware stack process
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->hasMiddleware()) {
            return $this->processMiddleware($request);
        }

        $handler = $this->route->getHandler();

        if (isImplementerOf($handler, RequestHandlerInterface::class)) {
            $handler = [$handler, 'handle'];
        }

        $queryParams = $request->getQueryParams() ?: [];
        $parameters = [
            'request' => $request,
            'params'  => $queryParams,
            'args'    => $this->route->getArguments()
        ];
        $parameters += $this->route->getArguments() + $queryParams;

        return $this->container->resolve($handler, $parameters);
    }

    /**
     * Set the route that will be handled
     *
     * @param RouteInterface $route
     * @return static
     */
    public function route(RouteInterface $route)
    {
        if (!$route->getHandler()) {
            throw new LogicException(sprintf(
                'No handler defined for found route [%s]', $route->getName())
            );
        }

        $this->route = $route;
        $this->middleware = $route->getMiddleware();

        return $this;
    }

    /**
     * Checks if the handler has middleware in its stack.
     *
     * @return bool
     */
    protected function hasMiddleware(): bool
    {
        return !empty($this->middleware);
    }

    /**
     * Process the middleware at the top of the stack.
     *
     * @param RequestInterface $request
     * @return ResponseInterface|null
     */
    protected function processMiddleware(ServerRequestInterface $request): ?ResponseInterface
    {
        if ($this->hasMiddleware()) {
            $middleware = array_shift($this->middleware);
            
            if ($middleware instanceof Closure) {
                $middleware = $middleware->bindTo($this->container);
            } elseif(isImplementerOf($middleware, MiddlewareInterface::class)) {
                $middleware = [$middleware, 'process'];
            }

            return $this->container->resolve($middleware, [
                'request' => $request, 
                'handler' => $handler
            ]);
        }
    }
}
