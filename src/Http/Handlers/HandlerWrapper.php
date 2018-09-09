<?php

namespace Framework\Http\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HandlerWrapper implements RequestHandlerInterface
{
    /**
     * The wrapped request handler
     *
     * @var RequestHandlerInterface|callable
     */
    protected $wrappedHandler;

    /**
     * Handler middleware stack
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Create a new handler wrapper instance
     *
     * @param RequestHandlerInterface|callable $handler
     */
    public function __construct($handler)
    {
        if (!is_callable($handler) && ! ($handler instanceof RequestHandlerInterface)) {
            throw new \InvalidArgumentException('Invalid handler wrapper request handler');
        }
        if ($handler instanceof RequestHandlerInterface) {
            $handler = [$handler, 'handle'];
        }
        $this->wrappedHandler = $handler;
    }
    
    /**
     * Process registered middleware and execute the wrapped request handler
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        while($middleware = array_shift($this->middleware)) {
            return is_callable($middleware) 
                ? $middleware($request, $this)
                : $middleware->process($request, $this);
        }
        return call_user_func(
            $this->wrappedHandler, 
            $request
        );
    }

    /**
     * Call the handle method
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request);
    }

    /**
     * Add middleware to the wrapper stack
     *
     * @param MiddlewareInterface|callable $middleware
     * @return static
     */
    public function add($middleware)
    {
        if (!is_callable($middleware) && ! ($middleware instanceof MiddlewareInterface)) {
            throw new \InvalidArgumentException('Invalid handler wrapper middleware');
        }
        $this->middleware[] = $middleware;

        return $this;
    }
}
