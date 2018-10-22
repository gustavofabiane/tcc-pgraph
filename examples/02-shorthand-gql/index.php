<?php

declare(strict_types=1);

use Framework\Core\Application;
use Framework\Router\RouteCollector;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ServerRequestInterface as Request;

use Framework\GraphQL\GraphQLProvider;
use GraphQL\Type\Definition\ObjectType;

use function Framework\Http\response;
use function Framework\Http\requestFromServerParams;
use function Framework\GraphQL\argument;
use function Framework\GraphQL\field;

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
                [argument('x', $app->typeRegistry->int()), 
                 argument('y', $app->typeRegistry->int(), 10)], 
                $resolve
            )
        ];
    }, 
]);

$app->config->set('graphql', [
    'query' => [
        'math' => $mathType
    ]
]);

(new GraphQLProvider)->provide($app);

/**
 * Run app with a server request implementation
 */
$app->run();
