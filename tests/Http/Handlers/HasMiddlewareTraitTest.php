<?php

namespace Framework\Tests\Http\Handlers;

use Framework\Http\Response;
use PHPUnit\Framework\TestCase;
use Framework\Container\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Http\Handlers\HasMiddlewareTrait;
use Framework\Http\Middleware\ResolvableMiddleware;

class HasMiddlewareTraitTest extends TestCase implements RequestHandlerInterface
{
    use HasMiddlewareTrait;

    public function setUp()
    {
        $this->resolver = $this->mockResolver();
        $this->middleware = [];
    }

    protected function mockResolver()
    {
        $container = new Container();
        return $container->getResolver();
    }

    public function testInvalidMiddleware()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->add('\Framework\Tests\Stubs\StubClass');
    }

    public function assertMiddlewareDataProvider()
    {
        return [
            [
                ['\Framework\Tests\Stubs\StubClass:middleware'],
                [new ResolvableMiddleware('\Framework\Tests\Stubs\StubClass:middleware', $this->mockResolver())]
            ],
            [
                [
                    '\Framework\Tests\testMiddlewareFunction',
                    function ($request, $handler) {
                        return $handler->handle($request);
                    }
                ],
                [
                    new ResolvableMiddleware(
                        function ($request, $handler) {
                            return $handler->handle($request);
                        },
                        $this->mockResolver()
                    ),
                    new ResolvableMiddleware('\Framework\Tests\testMiddlewareFunction', $this->mockResolver()),
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
                ['\Framework\Tests\Stubs\StubClass:middleware'],
                404, 'passed-middleware-stubclass'
            ],
            [
                [
                    '\Framework\Tests\testMiddlewareFunction',
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
        $response = $this->handle(\Framework\Tests\request('POST'));

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
