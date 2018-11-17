<?php

declare(strict_types=1);

use GraphQL\Error\Debug;
use Pgraph\Core\Application;
use Pgraph\Core\DefaultProvider;
use Pgraph\Router\RouteCollector;
use Pgraph\Router\RouterProvider;

use function Pgraph\GraphQL\field;
use function Pgraph\Http\response;

use Pgraph\GraphQL\GraphQLProvider;
use GraphQL\Type\Definition\ObjectType;
use function Pgraph\GraphQL\argument;
use Psr\Http\Message\ResponseInterface as Response;
use function Pgraph\Http\requestFromServerParams;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

include '../../vendor/autoload.php';

/**
 * Initializes application
 */
$app = new Application();
Application::setInstance($app);

$mathType = new ObjectType([
    'name' => 'Math',
    'description' => 'Do the maths!',
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
    'debug' => Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE,
    'query' => [
        'math' => [
            'type' => $mathType,
            'resolve' => function ($root, array $args = []) {
                return ['sum' => 'yes'];
            }
        ]
    ]
]);

$app->addProvider(DefaultProvider::class);
$app->addProvider(RouterProvider::class);
$app->addProvider(GraphQLProvider::class);

$app->typeRegistry->addType($mathType);

/**
 * Run app with a server request implementation
 */
$app->run();
