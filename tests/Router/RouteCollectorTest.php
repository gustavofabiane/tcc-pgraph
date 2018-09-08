<?php

namespace Framework\Tests\Router;

use Framework\Http\Response;
use FastRoute\RouteParser\Std;
use PHPUnit\Framework\TestCase;
use Framework\Container\Container;
use Framework\Router\RouteCollector;
use Framework\Router\RouteRequestHandler;
use FastRoute\DataGenerator\GroupCountBased;
use Framework\Http\Middleware\ResolvableMiddleware;
use Framework\Tests\Stubs\Middleware\XmlBody;
use Framework\Tests\Stubs\Middleware\ResponseWithErrorStatus;

class RouteCollectorTest extends TestCase
{
    /**
     * Container
     *
     * @var \Framework\Container\ContainerInterface
     */
    protected $container;

    /**
     * The route collector
     *
     * @var RouteCollector
     */
    protected $collector;

    public function setup()
    {
        $this->container = new Container();
        $this->collector = new RouteCollector(
            $this->container,
            new Std(), new GroupCountBased()
        );
    }

    public function testAddRouteClosure()
    {
        $middleware = function ($request, $handler) {
            return $handler->handle($request);
        };
        $handler = $this->collector->addRoute('GET', '/closure/{id:[0-9]+}', function ($id) {
            return (new Response())->withStatus((int) $id);
        })->add($middleware);

        $this->assertInstanceOf(RouteRequestHandler::class, $handler);
        $this->assertEquals(
            [new ResolvableMiddleware($middleware, $this->container)], 
            $handler->getMiddleware()
        );
    }

    public function testAddGroup()
    {
        $groupedRouteHandler = function ($id) {
            return (new Response())->withStatus((int) $id);
        };
        $this->collector->prefix('/prefix', function () use ($groupedRouteHandler) {
            $this->get('/ok', $groupedRouteHandler);
            $this->post('/ok-post', $groupedRouteHandler)->add(XmlBody::class);
        }, [XmlBody::class, ResponseWithErrorStatus::class]);

        $this->assertSame(false, empty($this->collector->getData())); 
        $this->assertEquals(
            [[
                'GET' => [
                    '/prefix/ok' => (new RouteRequestHandler($groupedRouteHandler, $this->container))
                                    ->middleware([XmlBody::class, ResponseWithErrorStatus::class])
                ],
                'POST' => [
                    '/prefix/ok-post' => (new RouteRequestHandler($groupedRouteHandler, $this->container))
                                        ->middleware([XmlBody::class, ResponseWithErrorStatus::class, XmlBody::class])
                ]
            ], []], 
            $this->collector->getData());
    }
}