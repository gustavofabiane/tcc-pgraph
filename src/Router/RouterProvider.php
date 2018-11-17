<?php

namespace Pgraph\Router;

use Pgraph\Core\Application;
use Pgraph\Core\ProviderInterface;
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
            $app->alias(RouteRequestHandler::class, 'routeHandler');
            $app->implemented(RouteRequestHandlerInterface::class, RouteRequestHandler::class);
        }

        if (!$app->has('routeCollector')) {
            $app->register('routeCollector', function () {
                return new RouteCollector();
            }, true);
        }

        if (!$app->has('routeParser')) {
            $app->register('routeParser', function () {
                return new RouteParser();
            });
        }
        
        if (!$app->has('routeDataGenerator')) {
            $app->register('routeDataGenerator', function () {
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
            $app->alias(Router::class, 'router');
            $app->implemented(RouterInterface::class, Router::class, true);
        }

        /**
         * Listen to route resolving event.
         */
        $app->registerListener('router', function (RouterInterface $router, Application $app) {
            
            /**
             * Application routes directory.
             */
            $routesDir = $app['config']->get('app', 'routes_dir');
            if (!$routesDir) {
                return;
            }

            /**
             * Collect application routes.
             */
            $router->collect(function () use ($routesDir) {
                
                /**
                 * Collect direct routes.
                 */
                require $routesDir . '/routes.php';

                /**
                 * Collect group file routes.
                 */
                $groups = glob($routesDir . '/*.php'); 
                foreach ($groups as $group) {
                    $groupPrefix = str_replace(
                        '.php', '', pathinfo($group, PATHINFO_FILENAME)
                    );
                    if ($groupPrefix != 'routes') {
                        $this->group('/' . $groupPrefix, function () use ($group) {
                            require $group;
                        });
                    }
                }
            });
        });
    }
}