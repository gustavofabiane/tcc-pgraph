<?php

namespace Pgraph\Tests\Http\Handlers;

use Pgraph\Http\Response;
use PHPUnit\Framework\TestCase;
use Pgraph\Container\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pgraph\Http\Handlers\HasMiddlewareTrait;
use Pgraph\Http\Middleware\ResolvableMiddleware;

class HasMiddlewareTraitTest extends TestCase implements RequestHandlerInterface
{
    use HasMiddlewareTrait;

    public function setUp()
    {
        $this->container = $this->mockContainer();
        $this->middleware = [];
    }

    protected function mockContainer()
    {
        return new Container();
    }

    public function testInvalidMiddleware()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->add('\Pgraph\Tests\Stubs\StubClass');
    }

    public function assertMiddlewareDataProvider()
    {
        return [
            [
                ['\Pgraph\Tests\Stubs\StubClass:middleware'],
                [new ResolvableMiddleware('\Pgraph\Tests\Stubs\StubClass:middleware', $this->mockContainer())]
            ],
            [
                [
                    '\Pgraph\Tests\testMiddlewareFunction',
                    function ($request, $handler) {
                        return $handler->handle($request);
                    }
                ],
                [
                    new ResolvableMiddleware(
                        function ($request, $handler) {
                            return $handler->handle($request);
                        },
                        $this->mockContainer()
                    ),
                    new ResolvableMiddleware('\Pgraph\Tests\testMiddlewareFunction', $this->mockContainer()),
                ]
            ],
        ];
    }

    /**
     * @dataProvider assertMiddlewareDataProvider
     *
     * @param array $middleware
     * @param array $expected
     * @return void
     */
    public function testAddMiddleware($middleware, $expected)
    {
        $this->middleware($middleware);
        $this->assertEquals($expected, $this->getMiddleware());
    }

    public function assertMiddlewareExecutionDataProvider()
    {
        return [
            [
                ['\Pgraph\Tests\Stubs\StubClass:middleware'],
                404, 'passed-middleware-stubclass'
            ],
            [
                [
                    '\Pgraph\Tests\testMiddlewareFunction',
                    function ($request, $handler) {
                        return $handler->handle($request->withAttribute('closure-middleware', 123456789));
                    }
                ],
                200, '123456789'
            ],
        ];
    }

    /**
     * @dataProvider assertMiddlewareExecutionDataProvider
     *
     * @param array $middleware
     * @param array $expected
     * @return void
     */
    public function testProcessMiddleware($middleware, $statusCodeExpected, $bodyExpected)
    {
        $this->middleware($middleware);
        $response = $this->handle(\Pgraph\Tests\request('POST'));

        $this->assertSame($statusCodeExpected, $response->getStatusCode());
        $this->assertEquals($bodyExpected, (string) $response->getBody());
    }

    /**
     * Handle middleware execution test
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->hasMiddleware()) {
            return $this->processMiddleware($request);
        }
        return new Response(404);
    }
}
