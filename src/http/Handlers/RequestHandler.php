<?php

namespace Framework\Http\Handlers;

use Framework\Http\Response;
use Framework\Container\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Container\ServiceResolverInterface;
use Framework\Http\Middleware\ClassMethodCallMiddleware;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * Dependency Container
     *
     * @var ServiceResolverInterface
     */
    protected $serviceResolver;

    /**
     * A to be used if not response is produced by the middlware stack
     *
     * @var RequestHandlerInterface
     */
    protected $fallbackHandler;

    /**
     * An array of MiddlewareInterface to be 
     * proccessed by the handler
     *
     * @var array
     */
    protected $middleware = [];

    public function __construct(
        ServiceResolverInterface $serviceResolver,
        RequestHandlerInterface $fallbackHandler
    ) {
        $this->serviceResolver = $serviceResolver;
        $this->fallbackHandler = $fallbackHandler;
    }

    /**
     * Handle the server request recieved and then 
     * returns a response after middlware stack process
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!empty($this->middleware)) {
            $middleware = array_shift($this->middleware);
            return $middleware->process($request, $this);
        }

        return $this->fallbackHandler->handle($request);
    }

    public function add($middleware)
    {
        if (!$middleware = $this->filterMiddleware($middleware)) {
            throw new \InvalidArgumentException(
                'Argument value is not a valid middleware'
            );
        }
        $this->middleware[] = $middleware;

        return $this;
    }

    public function middleware(array $middlewareGroup)
    {
        foreach ($middlewareGroup as $middleware) {
            $this->add($middleware);
        }
    }

    protected function filterMiddleware($middleware)
    {
        if (is_object($middleware) && 
            Container::implements($middleware, MiddlewareInterface::class)
        ) {
            return $middleware;
        }

        if (is_callable($middleware)) {
            return function () use ($middleware) {
                return new CallableMiddleware($middleware);
            };
        }

        if (class_exists($middleware) && 
            Container::implements($middleware, MiddlewareInterface::class)
        ) {
            return $this->serviceResolver->resolve($middleware, true);
        }

        if (preg_match(ServiceResolverInterface::RESOLVABLE_PATTERN, $middleware, $matches)) {
            $middleware = [$matches[1], $matches[2]];
        }
        if (is_array($middleware)) {
            return new ClassMethodCallMiddleware(
                $middleware[0], $middleware[1], $this->serviceResolver
            );
        }

        return false;
    }
}
