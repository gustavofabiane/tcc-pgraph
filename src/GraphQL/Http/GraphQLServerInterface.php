<?php

declare(strict_types=1);

namespace Pgraph\GraphQL\Http;

use Psr\Http\Message\ServerRequestInterface;
use GraphQL\Executor\ExecutionResult;

interface GraphQLServerInterface
{
    public function execute(ServerRequestInterface $request): ExecutionResult;
}