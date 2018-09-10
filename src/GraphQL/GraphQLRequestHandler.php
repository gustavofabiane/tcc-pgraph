<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Handle request for the GraphQL server.
 */
class GraphQLRequestHandler implements RequestHandlerInterface
{
    /**
     * Handle a GraphQL request.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        
    }
}
