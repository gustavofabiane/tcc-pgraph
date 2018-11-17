<?php

namespace Pgraph\Tests\Http;

use Pgraph\Http;
use Pgraph\Core\Application;
use PHPUnit\Framework\TestCase;
use function Pgraph\Tests\request;
use Pgraph\Http\ResponseStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Pgraph\Tests\Stubs\Middleware\XmlBody;
use Pgraph\Tests\Stubs\StubRequestHandler;
use Pgraph\Tests\Stubs\Middleware\ResponseWithErrorStatus;

class HttpFunctionsTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        $app = new Application();
        $app->registerDefaultProvider();
        
        Application::setInstance($app);
    }

    public function testResponseHelper()
    {
        $response = Http\response(ResponseStatusCode::INTERNAL_SERVER_ERROR, 'body-content-test');
        
        $this->assertSame(ResponseStatusCode::INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertSame(
            ResponseStatusCode::STATUS_REASON_PHRASES[ResponseStatusCode::INTERNAL_SERVER_ERROR],
            $response->getReasonPhrase()
        );
        $this->assertEquals('body-content-test', (string) $response->getBody());
    }

    public function testJsonResponse()
    {
        $response = Http\jsonResponse(['json' => true], 203);

        $this->assertSame(203, $response->getStatusCode());
        $this->assertEquals('{"json":true}', (string) $response->getBody());
    }

    public function testWrapHandlerClosures()
    {
        $handler = function (ServerRequestInterface $request) : ResponseInterface {
            $status = 200;
            if ($request->getMethod() === 'POST') {
                $status = 404;
            }
            return Http\response($status);
        };
        $handler = Http\wrap($handler, function ($request, $handler) {
            return $handler($request->withMethod('POST'));
        });

        $request = request('PUT');

        $response = $handler($request);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testWrapHandlerPsr15Implementations()
    {
        $wrappedHandler = Http\wrap(
            Http\wrap(
                new StubRequestHandler(), 
                new ResponseWithErrorStatus()
            ),
            new XmlBody()
        ); 
        $response = $wrappedHandler(request());

        $this->assertSame(ResponseStatusCode::INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals(XmlBody::XML, (string) $response->getBody());
    }
}
