<?php

namespace Pgraph\Core;

use Pgraph\Http\Request;
use Pgraph\Http\Response;
use Pgraph\Http\ResponseStatusCode;
use Pgraph\Http\Handlers\ErrorHandler;
use Pgraph\Container\ContainerInterface;
use Pgraph\Http\Handlers\NotFoundHandler;

class DefaultProvider implements ProviderInterface
{
    public function provide(Application $app)
    {
        if (!$app->has('request')) {
            $app->register('request', function () {
                return Request::createFromServerParams(
                    $_SERVER, $_POST, $_COOKIE, $_FILES
                );
            });
        }

        if (!$app->has('response')) {
            $app->register('response', function () {
                return new Response(ResponseStatusCode::OK);
            });
        }


        /**
         * Register the not found request handler
         */
        if (!$app->has('notFoundHandler')) {
            $app->register('notFoundHandler', function () {
                return new NotFoundHandler();
            });
            $app->alias('Pgraph\Http\Handlers\NotFoundHandler', 'notFoundHandler');
        }
        
        /**
         * Register the request error handler
         */
        if (!$app->has('errorHandler')) {
            $app->register('errorHandler', function () {
                return new ErrorHandler();
            });
            $app->alias('Pgraph\Http\Handlers\ErrorHandler', 'errorHandler');
            $app->implemented(
                'Pgraph\Http\Handlers\ErrorHandlerInterface',
                'Pgraph\Http\Handlers\ErrorHandler'
            );
        }
    }
}
