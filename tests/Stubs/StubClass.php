<?php

namespace Framework\Tests\Stubs;

use Framework\Http\Body;
use Framework\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StubClass implements StubInterface
{
    private $fooBar;

    public function __construct($bar = null, string $baz = 'ok', bool $foo = false)
    {
        $this->fooBar = $foo ? $bar . $baz : null;
    }

    public function getFooBar()
    {
        return $this->fooBar;
    }

    public function setFooBar($fooBar)
    {
        $this->fooBar = $fooBar;
        return $this;
    }

    public function method()
    {
        return "Implemented";
    }

    public function toResolve(int $number, $userDefinedParam)
    {
        return $number + $userDefinedParam;
    }

    public function toResolveDefault(int $number = 2, string $code = '400'): int
    {
        return $number * $code;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = new Body();
        $body->write('test-resolvable-request-handler-with-class:method-pattern');
        return new Response(200, [], $body);
    }

    public function middleware(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = new Response(404);
        $response->getBody()->write('passed-middleware-stubclass');

        return $response;
    }
}
