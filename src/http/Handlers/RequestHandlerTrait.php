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
     * An array of MiddlewareInterface to be
     * proccessed by the handler.
     *
     * @var array
     */
    protected $middleware = [];

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

    /**
     * Checks if the handler has middleware in its stack.
     *
     * @return bool
     */
    protected function hasMiddleware(): bool
    {
        return !empty($this->middleware);
    }

    /**
     * Process the middleware at the top of the stack.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    protected function processMiddleware(RequestInterface $request): ResponseInterface
    {
        $middleware = array_shift($this->middleware);
        return $middleware->process($request, $this);
    }

    /**
     * Add a middleware at the top of the stack.
     *
     * @param MiddlewareInterface|callable $middleware
     * @return void
     */
    public function add($middleware, bool $top = true): RequestHandler
    {
        if (!$middleware = $this->filterMiddleware($middleware)) {
            throw new \InvalidArgumentException(
                'Argument value is not a valid middleware'
            );
        }
        array_unshift($this->middleware, $middleware);

        return $this;
    }

    /**
     * Add a list of middleware.
     *
     * @see add()
     *
     * @param array $middlewareGroup
     * @return void
     */
    public function middleware(array $middlewareGroup): RequestHandler
    {
        foreach ($middlewareGroup as $middleware) {
            $this->add($middleware);
        }
        return $this;
    }

    /**
     * Filter a middleware argument.
     *
     * Expects an object, Closure or class string.
     *
     * Returns FALSE if the middleware is not valid
     *
     * @param object|callable|string $middleware
     * @return object|callable|bool
     */
    protected function filterMiddleware($middleware)
    {
        if (is_object($middleware) &&
            Container::implements($middleware, MiddlewareInterface::class)
        ) {
            return $middleware;
        }

        if (is_string($middleware) && class_exists($middleware) &&
            Container::implements($middleware, MiddlewareInterface::class)
        ) {
            return $this->serviceResolver->resolve($middleware);
        }

        if (is_callable($middleware) || $middleware instanceof \Closure ||
        //    (class_exists($middleware) &&  Container::implements($middleware, MiddlewareInterface::class)) ||
           (preg_match(ServiceResolverInterface::RESOLVABLE_PATTERN, $middleware, $matches))
        ) {
            return new ResolvableMiddleware($middleware, $this->serviceResolver);
        }

        return false;
    }
}
