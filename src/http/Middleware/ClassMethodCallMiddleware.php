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
    private $callable;

    public function __construct($class, $method, ServiceResolverInterface $serviceResolver)
    {
        $this->class = $class;
        $this->method = $method;
        $this->serviceResolver = $serviceResolver;
    }

    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (is_object($this->class) && 
            method_exists($this->class, $this->method)
        ) {
            return $this->class->{$this->method}($request, $handler);
        }
        
        if (is_object($this->class)) {
            return $this->class($request, $handler);
        }

        return $this->serviceResolver->resolve([$this->class, $this->method]);
    }
}