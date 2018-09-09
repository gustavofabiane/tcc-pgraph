<?php

namespace Framework\Http\Handlers;

use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Produces a response from given request and exception
 */
interface ErrorHandlerInterface
{
    /**
     * Produces a response by handling an exception 
     * throwed by the application middleware or process.
     *
     * @param ServerRequestInterface $request
     * @param Throwable $exception
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, Throwable $error): ResponseInterface;
}
