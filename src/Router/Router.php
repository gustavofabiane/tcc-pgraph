<?php

namespace Framework\Router;

use Closure;
use RuntimeException;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher\GroupCountBased;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Match a server request URI path with a set of routes.
 *
 * Based on nikic/fast-route.
 *
 * @method \Framework\Router\RouteInterface get(string $pattern, $handler)
 * @method \Framework\Router\RouteInterface post(string $pattern, $handler)
 * @method \Framework\Router\RouteInterface put(string $pattern, $handler)
 * @method \Framework\Router\RouteInterface patch(string $pattern, $handler)
 * @method \Framework\Router\RouteInterface head(string $pattern, $handler)
 * @method \Framework\Router\RouteInterface delete(string $pattern, $handler)
 * @method \Framework\Router\RouteInterface all(string $pattern, $handler)
 * @method \Framework\Router\RouteInterface group($routePrefix, callable $callback, array $middleware = [])
 *
 */
class Router implements RouterInterface
{
    /**
     * Route dispatcher.
     *
     * @var \FastRoute\Dispatcher
     */
    protected $dispatcher;

    /**
     * Route collector implementation
     *
     * @var RouteCollector
     */
    protected $routeCollector;

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
     * @param string|null $routesCacheFile
     */
    public function __construct(
        RouteCollector $routeCollector,
        array $routesFiles = null,
        ?string $routesCacheFile = null
    ) {
        $this->routeCollector = $routeCollector;
        $this->routesFiles = $routesFiles;
        $this->routesCacheFile = $routesCacheFile;
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
                $this->routeCollector
            );
        }
        $routeDefinitionCallback($this->routeCollector);
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
        if (method_exists($this->routeCollector, $name)) {
            return call_user_func_array(
                [$this->routeCollector, $name],
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
        $routeData = $this->dispatcher()->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );
        
        return new Route(
            $request->getUri()->getPath(),
            $routeData[0],
            isset($routeData[1]) && $routeData[1] instanceof RequestHandlerInterface
                ? $routeData[1]
                : null,
            $routeData[2] ?? null
        );
    }

    /**
     * Creates the route dispatcher instance.
     *
     * @return void
     */
    protected function dispatcher()
    {
        if (!$this->dispatcher) {
            $files = $this->routesFiles;
            if (!empty($files)) {
                $routeDefinitionCallback = function (RouteCollector $router) use ($files) {
                    foreach ($files as $file) {
                        require $file;
                    }
                };
            }

            $cacheDisabled = is_null($this->routesCacheFile);
    
            if (!$cacheDisabled && file_exists($this->routesCacheFile)) {
                $dispatchData = require $this->routesCacheFile;
                if (!is_array($dispatchData)) {
                    throw new RuntimeException(
                        sprintf('Invalid cache file \'%s\'', $this->routesCacheFile)
                    );
                }
            }
            
            if (!isset($dispatchData)) {
                if (isset($routeDefinitionCallback)) {
                    $routeDefinitionCallback($this->routeCollector);
                }

                /** @var RouteCollector $routeCollector */
                $dispatchData = $this->routeCollector->getData();
                if (!$cacheDisabled && $this->routeCollector->isCachable()) {
                    file_put_contents(
                        $this->routesCacheFile,
                        '<?php return ' . var_export($dispatchData, true) . ';'
                    );
                }
            }
    
            $this->dispatcher = new GroupCountBased($dispatchData);
        }
        return $this->dispatcher;
    }
}
