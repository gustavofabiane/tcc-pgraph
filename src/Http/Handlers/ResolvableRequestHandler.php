<?php

namespace Pgraph\Http\Handlers;

use Psr\Http\Message\ResponseInterface;
use Pgraph\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Treats a resolvable as a request handler
 */
class ResolvableRequestHandler implements RequestHandlerInterface
{
    use HasMiddlewareTrait;

    /**
     * A valid resolvable by the ServiceResolverInterface implementation
     *
     * @var Closure|string|object|callable
     */
    protected $resolvable;

    /**
     * Creates the resolvable request handler instance
     *
     * @param object|callable|string $resolvable
     * @param ServiceResolverInterface $resolver
     */
    public function __construct($resolvable, ContainerInterface $container)
    {
        $this->resolvable = $resolvable;
        $this->container = $container;
    }

    /**
     * Handle the server request recieved and then
     * returns a response after middleware stack process
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->hasMiddleware()) {
            return $this->processMiddleware($request);
        }

        $queryParams = $request->getQueryParams() ?: [];
        $parameters = [
            'request' => $request,
            'params' => $queryParams
        ];
        $parameters += $queryParams;

        return $this->container->resolve($this->resolvable, $parameters);
    }
}