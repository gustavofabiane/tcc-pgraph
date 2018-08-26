<?php

namespace Framework\Router;

use function Framework\isImplementerOf;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Http\Handlers\ResolvableRequestHandler;

/**
 * Handler for route callback execution
 */
class RouteRequestHandler extends ResolvableRequestHandler
{
    /**
     * Handle the server request recieved and then
     * returns a response after middleware stack process
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!isImplementerOf($this->resolvable)) {
            $this->resolvable = [$this->resolvable, 'handle'];
        }

        return parent::handle($request);
    }
}
