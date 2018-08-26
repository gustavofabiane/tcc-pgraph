<?php

use Framework\Http\Uri;
use Framework\Http\Body;
use Framework\Http\Request;
use PHPUnit\Framework\TestCase;
use Framework\Http\Handlers\NotFoundHandler;

class NotFoundHandlerTest extends TestCase
{
    /**
     * Request
     *
     * @var Request
     */
    protected $request;

    /**
     * Not found handler instance
     *
     * @var NotFoundHandler
     */
    protected $handler;

    public function setUp()
    {
        $body = new Body();
        $body->write('{"json": "This is a JSON object"}');

        $this->request = new Request(
            'GET',
            ['REQUEST_URI' => '/path/to/1', 'CONTENT_TYPE' => 'application/json'], 
            Uri::createFromString('https://examble.com/path/to/1'), 
            ['Accept' => 'application/json'], 
            [], $body, []
        );

        $this->handler = new NotFoundHandler();
    }

    public function testNotFoundAcceptJson()
    {
        $response = $this->handler->handle($this->request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertEquals(
            '{"message":"Resource not found"}', 
            $response->getBody()->getContents()
        );
    }

    public function testFallbackToTextPlainContentType()
    {
        $request = $this->request->withHeader('Accept', 'foo/bar');
        
        $response = $this->handler->handle($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertEquals('text/plain', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(
            'Resource not found', 
            $response->getBody()->getContents()
        );
    }
}
