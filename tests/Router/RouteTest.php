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
        $this->handler = function (ServerRequestInterface $request) {
            return (new Response())->withStatus(203);
        };
    }

    public function testInitializeRoute()
    {
        $route = new Route(['GET'], '/test', $this->handler, 'test-route');
        
        $this->assertFalse($route->isFound());
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertInstanceOf(\Closure::class, $route->getHandler());

        return $route;
    }
    
    /**
     * @depends testInitializeRoute
     *
     * @return void
     */
    public function testInvalidRouteStatus(RouteInterface $route)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Route status 999 is not valid');

        $route->setStatus(999);
    }

    /**
     * @depends testInitializeRoute
     *
     * @return void
     */
    public function testGetHandlerWithRoute(RouteInterface $route)
    {
        $this->assertEquals($this->handler, $route->getHandler());
    }

    /**
     * @depends testInitializeRoute
     *
     * @return void
     */
    public function testGetArguments(RouteInterface $route)
    {
        $route->setArguments(['id' => 123, 'name' => 'John']);
        $this->assertEquals(['id' => 123, 'name' => 'John'], $route->getArguments());
    }

    /**
     * @depends testInitializeRoute
     *
     * @return void
     */
    public function testFound(RouteInterface $route)
    {
        $route->setStatus(Dispatcher::FOUND);
        $this->assertSame(false, $route->isNotAllowed());
        $this->assertSame(true, $route->isFound());
    }
}
