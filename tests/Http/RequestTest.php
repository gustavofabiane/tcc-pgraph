<?php

namespace Framework\Tests\Http;

use Framework\Http\Uri;
use Framework\Http\Body;
use Framework\Http\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RequestTest extends TestCase
{
    /**
     * 
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * 
     *
     * @var array
     */
    protected $jsonBody = [
        'message' => 'testing arounds'
    ];

    public function setUp()
    {
        $body = new Body(fopen(__DIR__ . '/../utils/writable-stream', 'w+'));
        $body->rewind();
        $body->write(json_encode($this->jsonBody));

        $this->request = new Request(
            'GET', 
            [
                'HTTPS' => 'On',
                'HTTP_HOST' => 'localhost',
                'CONTENT_TYPE' => 'application/json',
                'REQUEST_URI' => '/test/1'
            ], 
            Uri::createFromString('http://localhost:8080/get/path?item=1'), 
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ueahdlsdjalweuheudhuwldhu12u3hlaiuhdu',
                'Host' => 'localhost'
            ],
            [],
            $body
        );
    }

    public function testPreserveHostHeader()
    {
        $originalHost = $this->request->getHeaderLine('Host');
        $request = $this->request->withUri($this->request->getUri()->withHost('127.0.0.1'), true);
        
        $this->assertEquals($originalHost, $request->getHeaderLine('Host'));   
    }

    public function testDoNotPreserveHostHeader()
    {
        $originalHost = $this->request->getHeaderLine('Host');
        $request = $this->request->withUri($this->request->getUri()->withHost('127.0.0.1'));
        
        $this->assertNotEquals($originalHost, $request->getHeaderLine('Host'));
        $this->assertEquals('127.0.0.1', $request->getHeaderLine('Host'));
    }
}
