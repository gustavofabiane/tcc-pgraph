<?php

namespace Framework\Http\Handlers;

use Framework\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
{

    /**
     * A to be used if not response is produced by the middlware stack
     *
     * @var RequestHandlerInterface
     */
    protected $fallbackHandler;

    public function __construct(RequestHandlerInterface $fallbackHandler)
    {
        $this->fallbackHandler = $fallbackHandler;
    }

    /**
     * An array of MiddlewareInterface to be 
     * proccessed by the handler
     *
     * @var array
     */
    protected $middleware = [];

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

    public function add(MiddlewareInterface $middleware)
    {
        $this->middleware[] = $middleware;

        return $this;
    }
}
