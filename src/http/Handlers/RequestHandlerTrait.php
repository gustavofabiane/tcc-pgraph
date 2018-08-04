<?php

namespace Framework\Http\Handlers;

use Exception;
use Framework\Http\Response;
use Framework\Container\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Container\ServiceResolverInterface;
use Framework\Http\Middleware\ResolvableMiddleware;
use Framework\Http\Handlers\ResolvableRequestHandler;
use Framework\Http\Handlers\ErrorRequestHandlerInterface;

trait RequestHandlerTrait
{
    use HasMiddlewareTrait;

    /**
     * Dependency Container
     *
     * @var ServiceResolverInterface
     */
    protected $serviceResolver;

    /**
     * A handler to be used if no response is produced by the middleware stack.
     *
     * @var RequestHandlerInterface
     */
    protected $nextHandler;

    /**
     * A handler used to handle errors throwed by the handler and middlware.
     *
     * @var ErrorRequestHandlerInterface
     */
    protected $errorHandler;

    /**
     * Creates the request handler instance.
     *
     * @param ServiceResolverInterface $serviceResolver
     *      The application service resolver implementation
     * @param RequestHandlerInterface $next
     *      The next handler to be execute if no response has been generated
     */
    public function __construct(
        ServiceResolverInterface $serviceResolver,
        RequestHandlerInterface $next,
        ErrorRequestHandlerInterface $errorHandler = null
    ) {
        $this->serviceResolver = $serviceResolver;
        $this->nextHandler = $next;
        $this->errorHandler = $errorHandler;
    }
}
