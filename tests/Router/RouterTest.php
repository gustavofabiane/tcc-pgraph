<?php

namespace Framework\Tests\Router;

use Framework\Router\Route;
use Framework\Http\Response;
use Framework\Router\Router;
use FastRoute\RouteParser\Std;
use PHPUnit\Framework\TestCase;
use Framework\Container\Container;
use Framework\Router\RouteCollector;
use Framework\Router\RouteDispatcher;
use Framework\Router\RouterInterface;
use function Framework\Tests\request;
use Framework\Router\RouteRequestHandler;
use FastRoute\DataGenerator\GroupCountBased;
use Psr\Http\Message\ServerRequestInterface;

class RouterTest extends TestCase
{
    /**
     * Route instance
     *
     * @var Router
     */
    protected $router;

    /**
     * container instance
     *
     * @var Container
     */
    protected $container;

    public function setup()
    {
        $collector = new RouteCollector();
        $this->router = new Router(
            $collector,
            new RouteDispatcher(
                $collector, new Std(), new GroupCountBased()
            )
        );
    }

    public function testCollectClosureBoundToCollector()
    {
        $this->router->collect(function () {
            TestCase::assertInstanceOf(RouteCollector::class, $this);
        });
    }

    public function testCallCollectorMethods()
    {
        $handler = function ($id) {
            return (new Response())->withHeader('Content-Type', 'plain/text');
        };
        $this->router->get('/get-route/{id}', $handler);

        $this->assertEquals(
            [
                new Route(['GET'], '/get-route/{id}', $handler, 'r-1')
            ],
            $this->router->getData()
        );
    }

    public function testMatchRoute()
    {
        $this->router->post('/match', function (ServerRequestInterface $request) {
            $response = new Response();
            $response->getBody()->write($request->getParsedBody()['name']);
            return $response->withStatus(203);
        });
        
        $route = $this->router->match(
            $request = request(
                'POST', ['Content-Type' => 'application/json'], 
                'http://localhost/match', '{"name":"John"}', [], []
            )
        );
        $this->assertTrue($route->isFound());
        
        $response = $route->getHandler()($request);
        $this->assertEquals('John', $response->getBody()->getContents());
        $this->assertSame(203, $response->getStatusCode());
    }

    public function testNotAllowedRoute()
    {
        $this->router->delete('/delete-something', function (ServerRequestInterface $request) {
            return new Response();
        });
        $route = $this->router->match(request('POST', [], '/delete-something'));

        $this->assertFalse($route->isFound());
        $this->assertTrue($route->isNotAllowed());
        $this->assertInternalType('null', $route->getHandler());
        $this->assertSame([], $route->getArguments());
    }

    public function testNotFoundRoute()
    {
        $this->router->all('/match-all', function () {
            return (new Response())->withStatus(500);
        });
        $route = $this->router->match(request('GET', [], '/match-arl'));

        $this->assertFalse($route->isFound());
        $this->assertFalse($route->isNotAllowed());
        $this->assertInternalType('null', $route->getHandler());
    }
}
