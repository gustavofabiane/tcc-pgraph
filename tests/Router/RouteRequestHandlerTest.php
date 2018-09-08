<?php

namespace Framework\Tests\Router;

use Framework\Http\Response;
use PHPUnit\Framework\TestCase;
use Framework\Container\Container;
use Framework\Router\RouteRequestHandler;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Router\Route;

class RouteRequestHandlerTest extends TestCase
{
    /**
     * Route Handler
     *
     * @var RouteRequestHandler
     */
    protected $handler;

    public function setup()
    {
    }
    
    public function testExecuteCallback()
    {
        $handler = new RouteRequestHandler(function (ServerRequestInterface $request) {
            return (new Response())->withStatus(200);
        }, new Container());
        
        $handler->setRoute(new Route('/test', \FastRoute\Dispatcher::FOUND, $handler));

        $response = $handler->handle(\Framework\Tests\request());
        $this->assertSame(200, $response->getStatusCode());
    }
}
