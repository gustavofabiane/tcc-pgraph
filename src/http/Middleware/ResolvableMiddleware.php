<?php

namespace Framework\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Framework\Container\ServiceResolver;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Container\ServiceResolverInterface;

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
     * @var ServiceResolverInterface
     */
    private $serviceResolver;

    public function __construct($resolvable, ServiceResolverInterface $serviceResolver)
    {
        $this->resolvable = $resolvable;
        $this->serviceResolver = $serviceResolver;
    }

    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return $this->serviceResolver->resolve(
            $this->resolvable, 
            false,
            compact('request', 'handler')
        );
    }
}