<?php

namespace Pgraph\Tests\Http\Handlers;

use Pgraph\Http\Uri;
use Pgraph\Http\Body;
use Pgraph\Http\Request;
use Pgraph\Http\Response;
use PHPUnit\Framework\TestCase;
use Pgraph\Container\Container;
use function Pgraph\Tests\request;
use Psr\Http\Message\ResponseInterface;
use Pgraph\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Pgraph\Http\Handlers\ResolvableRequestHandler;

class ResolvableRequestHandlerTest extends TestCase
{
    /**
     * Resolver instance
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Request used for tests
     *
     * @var ServerRequestInterface
     */
    protected $request;

    public function setUp()
    {
        $this->container = new Container();

        $this->request = request('POST');
    }

    protected function handler($resolvable)
    {
        return new ResolvableRequestHandler($resolvable, $this->container);
    }

    public function testHandleFunction()
    {
        $handler = $this->handler('\\Pgraph\\Tests\\testFunctionHandler');

        $response = $handler->handle($this->request);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertEquals('test-resolvable-request-handler-with-function', (string) $response->getBody());
    }

    public function testHandleClosure()
    {
        $closure = function (ServerRequestInterface $request): ResponseInterface {
            $body = new Body();
            $body->write('test-resolvable-request-handler-with-closure');
            return new Response(404, [], $body);
        };

        $handler = $this->handler($closure);

        $response = $handler->handle($this->request);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertEquals(
            'test-resolvable-request-handler-with-closure', 
            (string) $response->getBody()
        );
    }
    
    public function testHandleClassMethodCallable()
    {
        $resolvable = '\Pgraph\Tests\Stubs\StubClass:handle';
        
        $handler = $this->handler($resolvable);
        
        $response = $handler->handle($this->request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals(
            'test-resolvable-request-handler-with-class:method-pattern', 
            (string) $response->getBody()
        );
    }
}
