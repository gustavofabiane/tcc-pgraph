<?php

namespace Framework\Http\Handlers;

/**
 * Define behaviors for implementations that have middleware
 */
trait HasMiddlewareTrait
{
    /**
     * An array of MiddlewareInterface to be
     * proccessed by the handler.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Get the middleware stack.
     *
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
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
