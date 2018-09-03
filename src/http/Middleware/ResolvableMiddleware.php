<?php

namespace Framework\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Framework\Container\resolver;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
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
    private $resolver;

    public function __construct($resolvable, ServiceResolverInterface $resolver)
    {
        $this->resolvable = $resolvable;
        $this->resolver = $resolver;
    }

    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return $this->resolver->resolve(
            $this->resolvable, 
            compact('request', 'handler')
        );
    }
}