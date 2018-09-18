<?php

namespace Framework\GraphQL;

use Psr\Http\Message\ServerRequestInterface;
use GraphQL\Executor\ExecutionResult;

interface GraphQLServerInterface
{
    public function execute(ServerRequestInterface $request): ExecutionResult;
}