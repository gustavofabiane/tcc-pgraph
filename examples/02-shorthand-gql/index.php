<?php

declare(strict_types=1);

use Framework\Core\Application;
use Framework\Router\RouteCollector;
use Framework\Router\RouterProvider;
use function Framework\GraphQL\field;
use function Framework\Http\response;

use Framework\GraphQL\GraphQLProvider;
use GraphQL\Type\Definition\ObjectType;

use function Framework\GraphQL\argument;
use Psr\Http\Message\ResponseInterface as Response;
use function Framework\Http\requestFromServerParams;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

include '../../vendor/autoload.php';

/**
 * Initializes application
 */
$app = new Application();

$mathType = new ObjectType([
    'name' => 'Math',
    'fields' => function () use ($app) {
        $resolve = function ($src, $args, $context) {
            return $args['x'] + $args['y'];
        };
        return [
            field(
                $app->typeRegistry->int(), 'sum', 
                [
                    argument('x', $app->typeRegistry->nonNull('int')), 
                    argument('y', $app->typeRegistry->int(), 10)
                ], 
                $resolve
            )
        ];
    }, 
]);

$app->config->set('graphql', [
    // 'debug' => 0,
    'query' => [
        'math' => $mathType
    ]
]);

$app->addProvider(new RouterProvider());
$app->addProvider(new GraphQLProvider());

/**
 * Run app with a server request implementation
 */
$app->run();
