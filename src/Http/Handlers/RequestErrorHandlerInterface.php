<?php

namespace Framework\Http\Handlers;

use Throwable;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Produces a response from given request and exception
 */
interface ErrorRequestHandlerInterface extends RequestHandlerInterface
{
    /**
     * Produces a response by handling an exception 
     * throwed by the application middleware or process.
     *
     * @param ServerRequestInterface $request
     * @param Exception $exception
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, Throwable $error): ResponseInterface;
}
