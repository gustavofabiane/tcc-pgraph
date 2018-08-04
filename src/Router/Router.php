<?php

namespace Framework\Router;

use Psr\Http\Message\ServerRequestInterface;

class Router implements RouterInterface
{
    /**
     * Route dispatcher.
     *
     * @var \FastRoute\Dispatcher
     */
    protected $dispatcher;

    /**
     * Dispatcher type.
     * 
     * Can assume value of 'simple' or 'cached'
     *
     * @var string
     */
    protected $dispatcherType;

    /**
     * An array with all routes files registered.
     *
     * @var array
     */
    protected $routesFiles;

    /**
     * The file where to store the route cache.
     *
     * @var string
     */
    protected $routesCacheFile;

    /**
     * Creates a new Router instance.
     *
     * @param string $dispatcherType
     * @param array $routesFiles
     * @param string|null $routesCacheFile
     */
    public function __construct(string $dispatcherType, array $routesFiles, ?string $routesCacheFile = null)
    {
        $this->dispatcherType = $dispatcherType;
        $this->routesFiles = $routesFiles;
        $this->routesCacheFile = $routesCacheFile;
    }

    /**
     * Matches the given request to a specified route.
     *
     * @param ServerRequestInterface $request
     * @return RouteInterface
     */
    public function match(ServerRequestInterface $request): RouteInterface
    {
        $this->makeDispatcher();

        $routeData = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        $route = $routeData[1];

        $route->setStatus($routeData[0]);
        $route->setArguments($route[2]);

        return $route;
    }
}
