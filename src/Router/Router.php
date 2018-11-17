<?php

namespace Pgraph\Router;

use Closure;
use RuntimeException;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Match a server request URI path with a set of routes.
 *
 * Based on nikic/fast-route.
 *
 * @method \Pgraph\Router\RouteInterface get(string $pattern, $handler)
 * @method \Pgraph\Router\RouteInterface post(string $pattern, $handler)
 * @method \Pgraph\Router\RouteInterface put(string $pattern, $handler)
 * @method \Pgraph\Router\RouteInterface patch(string $pattern, $handler)
 * @method \Pgraph\Router\RouteInterface head(string $pattern, $handler)
 * @method \Pgraph\Router\RouteInterface delete(string $pattern, $handler)
 * @method \Pgraph\Router\RouteInterface all(string $pattern, $handler)
 * @method \Pgraph\Router\RouteInterface group($routePrefix, callable $callback, array $middleware = [])
 *
 */
class Router implements RouterInterface
{
    /**
     * Route dispatcher.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Route collector implementation
     *
     * @var RouteCollectorInterface
     */
    protected $collector;

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
     * Create a new Router instance.
     *
     * @param RouteCollector $routeCollector
     * @param array $routesFiles
     */
    public function __construct(RouteCollectorInterface $collector, Dispatcher $dispatcher) 
    {
        $this->collector = $collector;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Collect routes defined in the given callback
     *
     * @param callable $routeDefinitionCallback
     * @return void
     */
    public function collect(callable $routeDefinitionCallback)
    {
        if ($routeDefinitionCallback instanceof Closure) {
            $routeDefinitionCallback = $routeDefinitionCallback->bindTo(
                $this->collector
            );
        }
        $routeDefinitionCallback($this->collector);
    }

    /**
     * Call the route collector method if the method does not exists in router
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \RuntimeException if the called method does not exists in route collector
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->collector, $name)) {
            return call_user_func_array(
                [$this->collector, $name],
                $arguments
            );
        }

        throw new RuntimeException(
            sprintf('Undefined method \'%d\' called for %s', $name, __CLASS__)
        );
    }

    /**
     * Matches the given request to a specified route.
     *
     * @param ServerRequestInterface $request
     * @return RouteInterface
     */
    public function match(ServerRequestInterface $request): RouteInterface
    {
        $routeData = $this->dispatcher->dispatch(
            $request->getMethod(),
            $uri = $request->getUri()->getPath()
        );

        switch ($routeData[0]) {
            case Dispatcher::FOUND:
                $arguments = $routeData[2];
                array_walk($arguments, function (&$arg) {
                    $arg = urldecode($arg);
                });
                return $this->collector->getRoute($routeData[1])
                    ->setArguments($arguments)
                    ->setStatus(Dispatcher::FOUND)
                    ->setPath($uri);
            
            case Dispatcher::METHOD_NOT_ALLOWED:
                return (new Route())
                    ->setStatus(Dispatcher::METHOD_NOT_ALLOWED)
                    ->setMethods($routeData[1]);
            
            default:
                return (new Route())
                    ->setStatus(Dispatcher::NOT_FOUND);
        }
    }
}
