<?php

namespace Framework\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Framework\Container\ServiceResolver;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Container\ServiceResolverInterface;

class ClassMethodCallMiddleware implements MiddlewareInterface
{
    /**
     * Middleware process
     *
     * @var string|object
     */
    private $class;

    /**
     * Method to be called
     *
     * @var string
     */
    private $method;

    /**
     * Resolver
     *
     * @var ServiceResolverInterface
     */
    private $serviceResolver;

    public function __construct($class, ?string $method, ServiceResolverInterface $serviceResolver)
    {
        if (!is_object($class) && !class_exists($class)) {
            throw new \InvalidArgumentException(
                __CLASS__ . ' constructor argument\'s 1 accepts only objects or class string.'
            );
        }
        $this->class = $class;
        $this->method = $method;
        $this->serviceResolver = $serviceResolver;
    }

    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $middleware = $this->class;
        if ($this->method) {
            $middleware = [$this->class, $this->method];
        }

        return $this->serviceResolver->resolve(
            $middleware, 
            false,
            compact('request', 'handler')
        );
    }
}