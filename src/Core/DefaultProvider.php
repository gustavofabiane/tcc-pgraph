<?php

namespace Framework\Core;

use Framework\Router\Router;
use Framework\Router\RouteCollector;
use Framework\Container\ContainerInterface;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\DataGenerator\GroupCountBased as RouteDataGenerator;

class DefaultProvider implements ProviderInterface
{
    public function provide(Application $app)
    {
        if (!$app->has('routeCollector')) {
            $app->register('routeCollector', function (ContainerInterface $c) {
                return new RouteCollector(
                    $c, new RouteParser(), new RouteDataGenerator()
                );
            }, true);
        }

        /**
         * Register router
         */
        if (!$app->has('router')) {
            $app->register('router', function (ContainerInterface $c) {
                return new Router(
                    $c->get('routeCollector'), 
                    $c->get('settings')['router']['routesFile'] ?? [],
                    $c->get('settings')['router']['routesCacheFile'] ?? null
                );
            }, true);
            $app->alias('Framework\Router\Router', 'router');
            $app->implemented('Framework\Router\RouterInterface', 'Framework\Router\Router', true);
        }

        /**
         * Register the not found request handler
         */
        if (!$app->has('notFoundHandler')) {
            $app->register('notFoundHandler', function () {
                return new \Framework\Http\Handlers\NotFoundHandler();
            });
            $app->alias('Framework\Http\Handlers\NotFoundHandler', 'notFoundHandler');
        }
        
        /**
         * Register the request error handler
         */
        if (!$app->has('errorHandler')) {
            $app->register('errorHandler', function () {
                return new \Framework\Http\Handlers\ErrorHandler();
            });
            $app->alias('Framework\Http\Handlers\ErrorHandler', 'errorHandler');
            $app->implemented(
                'Framework\Http\Handlers\ErrorHandlerInterface',
                'Framework\Http\Handlers\ErrorHandler'
            );
        }
    }
}
