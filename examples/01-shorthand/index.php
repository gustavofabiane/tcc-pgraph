<?php

use Pgraph\Core\Application;
use Pgraph\Router\RouteCollector;
use Pgraph\Router\RouterProvider;
use function Pgraph\Http\response;
use Psr\Http\Message\ResponseInterface as Response;

use function Pgraph\Http\requestFromServerParams;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

include '../../vendor/autoload.php';

/**
 * Initializes application
 */
$app = new Application();
$app->addProvider(new RouterProvider());

/**
 * Simple application middleware example
 */
$app->add(function (Request $request, Handler $handler): Response {
    return $handler->handle($request->withAttribute('shorthand', true));
});

/**
 * Collects route definitions
 */
$app->router->collect(function (RouteCollector $router): void {
    
    /**
     * Check if middleware changed the request by adding 
     */
    $router->get('/simple', function (Request $request): Response {
        if ($request->getAttribute('shorthand')) {
            return response(
                200, 'OK - Middleware worked!', 
                ['Content-Type' => 'text/plain']
            );
        }
        return response(
            500, 'Oops! App middleware was not executed.', 
            ['Content-Type' => 'text/plain']
        );
    });

    /**
     * Return a response with {name} in its body
     */
    $router->get('/{name}', function (string $name): Response {
        return response(
            200, sprintf('Hi, %s', $name), 
            ['Content-Type' => 'text/plain']
        );
    });
});

/**
 * Run app with a server request implementation
 */
$app->run(requestFromServerParams());
