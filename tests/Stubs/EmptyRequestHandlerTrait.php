<?php

namespace Framework\Tests\Stubs;

use Framework\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Http\Handlers\HasMiddlewareTrait;

/**
 * Empty handle method
 */
trait EmptyRequestHandlerTrait
{
    use HasMiddlewareTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if($this->hasMiddleware()) {
            return $this->processMiddleware($request);
        }
        return new Response(404);
    }
}
