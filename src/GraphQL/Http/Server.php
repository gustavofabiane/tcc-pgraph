<?php

declare(strict_types=1);

namespace Framework\GraphQL\Http;

use GraphQL\Server\StandardServer;
use GraphQL\Executor\ExecutionResult;
use Psr\Http\Message\ServerRequestInterface;


class Server extends StandardServer implements GraphQLServerInterface
{
    public function execute(ServerRequestInterface $request): ExecutionResult
    {
        return $this->executePsrRequest($request);
    }
}