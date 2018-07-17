<?php

namespace Framework\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Framework\Container\ServiceResolver;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Container\ServiceResolverInterface;

class CallableMiddleware implements MiddlewareInterface
{
    /**
     * Middleware process
     *
     * @var \Closure|callable
     */
    private $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return call_user_func(
            $this->callable, $request, $handler
        );
    }
}