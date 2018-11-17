<?php

namespace Pgraph\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Pgraph\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResolvableMiddleware implements MiddlewareInterface
{
    /**
     * A valid resolvable
     * 
     * Can be a Closure, callable, invokable object, 
     * or a class string, i.e, class:method
     *
     * @var string|object|callable
     */
    private $resolvable;

    /**
     * Resolver
     *
     * @var Container
     */
    private $container;

    /**
     * Creates a new resolvable middleware instance
     *
     * @param mixed $resolvable
     * @param ContainerInterface $container
     */
    public function __construct($resolvable, ContainerInterface $container)
    {
        $this->resolvable = $resolvable;
        $this->container = $container;
    }

    /**
     * Process the resolvable as middleware of a request handler
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return $this->container->resolve(
            $this->resolvable, 
            compact('request', 'handler')
        );
    }
}