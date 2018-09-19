<?php

declare(strict_types=1);

namespace Framework\GraphQL\Http;

use Psr\Http\Message\ServerRequestInterface;
use GraphQL\Executor\ExecutionResult;

interface GraphQLServerInterface
{
    public function execute(ServerRequestInterface $request): ExecutionResult;
}