<?php

namespace Pgraph\Tests;

use Pgraph\Http\Uri;
use Pgraph\Http\Body;
use Pgraph\Http\Request;
use Pgraph\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

function request($method = 'GET', $headers = [], $uri = '', $bodyContent = '', $serverParams = [], $files = [], $cookies = []): ServerRequestInterface
{
    $body = new Body('php://temp', 'r+');
    $body->write($bodyContent);
    return new Request(
        $method, 
        $serverParams, 
        Uri::createFromString($uri), 
        $headers,
        $cookies,
        $body,
        []
    );
}

function testFunctionHandler(ServerRequestInterface $request): ResponseInterface
{
    $body = new Body();
    $body->write('test-resolvable-request-handler-with-function');
    return new Response(400, [], $body);
}

function testMiddlewareFunction(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
{
    $response = new Response();
    $response->getBody()->write($request->getAttribute('closure-middleware', 987654321));
    return $response;
}