<?php

namespace Pgraph\Tests\Stubs\Middleware;

use Pgraph\Http\Body;
use Pgraph\Http\ResponseStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class XmlBody implements MiddlewareInterface
{
    const XML = '<root><name>Test</name><description>A test of middleware and handler</description></root>';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $body = new Body();
        $body->write(static::XML);

        return $response->withBody($body)->withHeader('Content-Type', 'application/xml');
    }
}