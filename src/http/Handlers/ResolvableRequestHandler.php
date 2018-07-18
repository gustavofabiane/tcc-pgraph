<?php

namespace Framework\Http\Handlers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Framework\Container\ServiceResolverInterface;

class ResolvableRequestHandler extends RequestHandler
{
    /**
     * A valid resolvable by the ServiceResolverInterface implementation
     *
     * @var Closure|string|object|callable
     */
    private $resolvable;

    /**
     * Creates the resolvable request handler instance
     *
     * @param object|callable|string $resolvable
     * @param ServiceResolverInterface $resolver
     */
    public function __construct($resolvable, ServiceResolverInterface $resolver)
    {
        $this->resolvable = $resolvable;
        $this->serviceResolver = $resolver;
    }

    /**
     * Handle the server request recieved and then
     * returns a response after middleware stack process
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        if (!$this->hasMiddleware()) {
            return $this->processMiddleware($request);
        }

        $queryParams = $request->getQueryParams();
        $parameters = [
            'request' => $request,
            'params' => $queryParams
        ];
        $parameters += $queryParams;

        return $this->serviceResolver->resolve(
            $resolvable, false, 
            $parameters
        );
    }
}