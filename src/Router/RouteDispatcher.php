<?php

namespace Pgraph\Router;

use LogicException;
use FastRoute\RouteParser;
use FastRoute\DataGenerator;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector as FastRouteCollector;

class RouteDispatcher extends GroupCountBased
{
    protected $collector;
    protected $internalCollector;
    protected $cacheFile;

    protected $prepared = false;

    public function __construct(
        RouteCollectorInterface $collector,
        RouteParser $parser,
        DataGenerator $generator,
        ?string $cacheFile = null
    ) {
        $this->collector = $collector;
        $this->cacheFile = $cacheFile;

        $this->internalCollector = new FastRouteCollector($parser, $generator);
    }

    protected function prepare(): void 
    {
        if (!$this->prepared) {
            $routeDefinitionCallback = function (FastRouteCollector $rc): void {
                /** @var RouteInterface $route */
                foreach ($this->collector->getData() as $route) {
                    $rc->addRoute($route->getMethods(), $route->getPattern(), $route->getName());
                }
            };

            $cacheDisabled = is_null($this->cacheFile);
    
            if (!$cacheDisabled && file_exists($this->cacheFile)) {
                $dispatchData = require $this->cacheFile;
                if (!is_array($dispatchData)) {
                    throw new LogicException(
                    sprintf('Invalid cache file \'%s\'', $this->cacheFile)
                );
                }
            }
            
            if (!isset($dispatchData)) {
                
                $routeDefinitionCallback($this->internalCollector);
                $dispatchData = $this->internalCollector->getData();

                if (!$cacheDisabled && $this->collector->isCacheable()) {
                    file_put_contents(
                        $this->cacheFile,
                        '<?php return ' . var_export($dispatchData, true) . ';'
                    );
                }
            }

            [$this->staticRouteMap, $this->variableRouteData] = $dispatchData;
            $this->prepared = true;
        }
    }

    public function dispatch($httpMethod, $uri): array
    {
        $this->prepare();
        return parent::dispatch($httpMethod, $uri);
    }
}
