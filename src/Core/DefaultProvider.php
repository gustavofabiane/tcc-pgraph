<?php

namespace Framework\Core;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Http\ResponseStatusCode;
use Framework\Container\ContainerInterface;

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
