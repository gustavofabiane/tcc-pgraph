<?php

namespace Framework\Router;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher\GroupCountBased;
use Psr\Http\Message\ServerRequestInterface;
use FastRoute\RouteParser\Std as RouteParser;
use Framework\Container\ServiceResolverInterface;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;

class Router implements RouterInterface
{
    /**
     * Route dispatcher.
     *
     * @var \FastRoute\Dispatcher
     */
    protected $dispatcher;

    /**
     * Resolver instance.
     *
     * @var ServiceResolverInterface
     */
    protected $resolver;

    /**
     * Route collector implementation
     *
     * @var RouteCollector
     */
    protected $routeCollector;

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
    public function __construct(
        ServiceResolverInterface $resolver,
        RouteCollector $routeCollector,
        string $dispatcherType,
        array $routesFiles,
        ?string $routesCacheFile = null
    ) {
        $this->resolver = $resolver;
        $this->routeCollector = $routeCollector;
        $this->dispatcherType = $dispatcherType;
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
                [$this->routeCollector, $name], $arguments
            );
        }

        throw new \RuntimeException(
            sprintf('Undefined method \'%s\' called for %s', $name, __CLASS__)
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
        $this->makeDispatcher();

        $routeData = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        return new Route($routeData[0], $routeData[1], $routeData[2]);
    }

    /**
     * Creates the route dispatcher instance.
     *
     * @return void
     */
    protected function makeDispatcher()
    {
        if (!$this->dispatcher) {
            $files = $this->routesFiles;
            $routeDefinitionCallback = function (RouteCollector $router) use ($files) {
                foreach ($files as $file) {
                    require $file;
                }
            };

            $cacheDisabled = is_null($this->routesCacheFile);
    
            if (!$cacheDisabled && file_exists($this->routesCacheFile)) {
                $dispatchData = require $this->routesCacheFile;
                if (!is_array($dispatchData)) {
                    throw new \RuntimeException(
                        sprintf('Invalid cache file \'%s\'', $this->routesCacheFile)
                    );
                }
            }
            
            if (!isset($dispatchData)) {
                $routeDefinitionCallback($this->routeCollector);

                /** @var RouteCollector $routeCollector */
                $dispatchData = $this->routeCollector->getData();
                if (!$cacheDisabled) {
                    file_put_contents(
                        $this->routesCacheFile,
                        '<?php return ' . var_export($dispatchData, true) . ';'
                    );
                }
            }
    
            $this->dispatcher = new GroupCountBased($dispatchData);
        }
    }
}
