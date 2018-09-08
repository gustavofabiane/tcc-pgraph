<?php

namespace Framework\Tests\Router;

use FastRoute\Dispatcher;
use Framework\Router\Route;
use Framework\Http\Response;
use PHPUnit\Framework\TestCase;
use Framework\Container\Container;
use Framework\Router\RouteInterface;
use Framework\Router\RouteRequestHandler;
use Psr\Http\Message\ServerRequestInterface;

class RouteTest extends TestCase
{
    /**
     * Route handler
     *
     * @var RouteRequestHandler
     */
    protected $handler;

    public function setup()
    {
        $this->handler = new RouteRequestHandler(function (ServerRequestInterface $request) {
            return (new Response())->withStatus(203);
        }, (new Container()));
    }

    public function testInitializeRoute()
    {
        $route = new Route('/test', Dispatcher::FOUND, $this->handler, ['id' => 123]);
        
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame(true, $route->found());
        $this->assertSame(false, $route->notAllowed());
        $this->assertEquals(['id' => 123], $route->getArguments());
        $this->assertInstanceOf(RouteRequestHandler::class, $route->getHandler());
    }
    
    public function testInvalidRouteStatus()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Route status 999 is not valid');

        $route = new Route('/test', 999);
    }

    public function testGetHandlerWithRoute()
    {
        $route = new Route('/test', Dispatcher::FOUND, clone $this->handler);
        $this->assertNotEquals($this->handler, $route->getHandler());
    }

    public function testGetArguments()
    {
        $route = new Route('/test', Dispatcher::FOUND, $this->handler, ['id' => 123, 'name' => 'John']);
        $this->assertEquals(['id' => 123, 'name' => 'John'], $route->getArguments());
    }

    public function testFound()
    {
        $route = new Route('/test', Dispatcher::FOUND);

        $this->assertSame(false, $route->notAllowed());
        $this->assertSame(true, $route->found());
    }
}
