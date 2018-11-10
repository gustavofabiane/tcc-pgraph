<?php

namespace Framework\Router;

use Framework\Core\Application;
use Framework\Core\ProviderInterface;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\DataGenerator\GroupCountBased as RouteDataGenerator;

class RouterProvider implements ProviderInterface
{
    public function provide(Application $app)
    {
        if (!$app->has('routeHandler')) {
            $app->register('routeHandler', function(Application $app) {
                return new RouteRequestHandler($app);
            });
        }

        if (!$app->has('routeCollector')) {
            $app->register('routeCollector', function (Application $c) {
                return new RouteCollector();
            }, true);
        }

        if (!$app->has('routeParser')) {
            $app->register('routeParser', function (Application $c) {
                return new RouteParser();
            });
        }
        
        if (!$app->has('routeDataGenerator')) {
            $app->register('routeDataGenerator', function (Application $c) {
                return new RouteDataGenerator();
            });
        }

        if (!$app->has('routeDispatcher')) {
            $app->register('routeDispatcher', function (Application $c) {
                return new RouteDispatcher(
                    $c->get('routeCollector'), 
                    $c->get('routeParser'), 
                    $c->get('routeDataGenerator'), 
                    $c->get('config')->get('app', 'routes_cache_file') ?? null
                );
            }, true);
        }

        /**
         * Register router
         */
        if (!$app->has('router')) {
            $app->register('router', function (Application $c) {
                return new Router(
                    $c->get('routeCollector'), 
                    $c->get('routeDispatcher')
                );
            }, true);
            $app->alias('Framework\Router\Router', 'router');
            $app->implemented('Framework\Router\RouterInterface', 'Framework\Router\Router', true);
        }
    }
}