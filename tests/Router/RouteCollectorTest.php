<?php

namespace Pgraph\Tests\Router;

use Pgraph\Http\Response;
use FastRoute\RouteParser\Std;
use PHPUnit\Framework\TestCase;
use Pgraph\Container\Container;
use Pgraph\Router\RouteCollector;
use Pgraph\Router\RouteInterface;
use Pgraph\Router\RouteRequestHandler;
use FastRoute\DataGenerator\GroupCountBased;
use Pgraph\Tests\Stubs\Middleware\XmlBody;
use Pgraph\Http\Middleware\ResolvableMiddleware;
use Pgraph\Tests\Stubs\Middleware\ResponseWithErrorStatus;
use Pgraph\Router\Route;

class RouteCollectorTest extends TestCase
{
    /**
     * Container
     *
     * @var \Pgraph\Container\ContainerInterface
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
        $route = $this->collector->route('GET', '/closure/{id:[0-9]+}', function ($id) {
            return (new Response())->withStatus((int) $id);
        })->add($middleware);

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertEquals(
            [$middleware], 
            $route->getMiddleware()
        );
    }

    public function testAddGroup()
    {
        $groupedRouteHandler = function ($id) {
            return (new Response())->withStatus((int) $id);
        };
        $this->collector->group('/prefix', function () use ($groupedRouteHandler) {
            $this->get('/ok', $groupedRouteHandler);
            $this->post('/ok-post', $groupedRouteHandler)->add(XmlBody::class);
        }, [XmlBody::class, ResponseWithErrorStatus::class]);

        $this->assertSame(false, empty($this->collector->getData())); 
        $this->assertEquals(
            [
                (new Route(['GET'], '/prefix/ok', $groupedRouteHandler, 'r-1'))->middleware([XmlBody::class, ResponseWithErrorStatus::class]),
                (new Route(['POST'], '/prefix/ok-post', $groupedRouteHandler, 'r-2'))->middleware([XmlBody::class, ResponseWithErrorStatus::class, XmlBody::class]),
            ], 
            $this->collector->getData()
        );
    }
}