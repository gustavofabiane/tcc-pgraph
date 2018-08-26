<?php

namespace Framework\Tests\Http\Handlers;

use Framework\Http\Uri;
use Framework\Http\Body;
use Framework\Http\Request;
use Framework\Http\Response;
use PHPUnit\Framework\TestCase;
use Framework\Container\Container;
use function Framework\Tests\request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Container\ServiceResolverInterface;
use Framework\Http\Handlers\ResolvableRequestHandler;

class ResolvableRequestHandlerTest extends TestCase
{
    /**
     * Resolver instance
     *
     * @var ServiceResolverInterface
     */
    protected $resolver;

    /**
     * Request used for tests
     *
     * @var ServerRequestInterface
     */
    protected $request;

    public function setUp()
    {
        $this->resolver = (new Container())->getResolver();

        $this->request = request('POST');
    }

    protected function handler($resolvable)
    {
        return new ResolvableRequestHandler($resolvable, $this->resolver);
    }

    public function testHandleFunction()
    {
        $handler = $this->handler('\\Framework\\Tests\\testFunctionHandler');

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
        $resolvable = '\Framework\Tests\Stubs\StubClass:handle';
        
        $handler = $this->handler($resolvable);
        
        $response = $handler->handle($this->request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals(
            'test-resolvable-request-handler-with-class:method-pattern', 
            (string) $response->getBody()
        );
    }
}
