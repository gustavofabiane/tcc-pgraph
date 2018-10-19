<?php

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

$sumType = new ObjectType([
    'name' => 'Sum',
    'fields' => function () use ($app) {
        
        $resolve = function ($src, $args, $context) {
            return 5 + $args['y'];
        };
        return [
            field($app->typeRegistry->int(), 'x', [argument('y', $app->typeRegistry->int(), 10)], $resolve)
        ];
    },
    'resolveField' => function () {
        return 321;
    }
]);

$app->config->set('graphql', [
    'query' => [
        'sum' => $sumType
    ],
    // 'unreachable_types' => [$sumType]
]);

(new GraphQLProvider)->provide($app);

/**
 * Run app with a server request implementation
 */
$app->run(requestFromServerParams());
