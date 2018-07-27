<?php

namespace Framework\Tests\Http;

use Framework\Http\Uri;
use Framework\Http\Body;
use Framework\Http\Request;
use PHPUnit\Framework\TestCase;
use Framework\Tests\Stubs\StubClass;
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
        'message' => 'testing around'
    ];

    protected $xmlBody = '<root><message code="10">testing around</message></root>';

    public function setUp()
    {
        /**
         * Set up superglobals
         */
        $_POST = ['test' => 'post', 'request' => 'form-url-encoded'];
        $serverParams = [
            'HTTPS' => 'On',
            'HTTP_HOST' => 'localhost',
            'CONTENT_TYPE' => 'application/json',
            'REQUEST_URI' => '/test/1',
            'QUERY_STRING' => 'server=true&item=2'
        ];

        /**
         * Creates request base for tests
         */
        $body = new Body(fopen(__DIR__ . '/../utils/writable-stream', 'w+'));
        $body->rewind();
        $body->write(json_encode($this->jsonBody));

        $this->request = new Request(
            'GET', 
            $serverParams, 
            Uri::createFromString('http://localhost:8080/get/path?item=1'), 
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ueahdlsdjalweuheudhuwldhu12u3hlaiuhdu',
                'Host' => 'localhost'
            ],
            [],
            $body,
            []
        );
    }

    /**
     * Tests if the implementation preserve the host 
     * header in when the request URI changes
     *
     * @return void
     */
    public function testPreserveHostHeader()
    {
        $originalHost = $this->request->getHeaderLine('Host');
        $request = $this->request->withUri($this->request->getUri()->withHost('127.0.0.1'), true);
        
        $this->assertEquals($originalHost, $request->getHeaderLine('Host'));   
    }

    /**
     * Tests if the implementation expects not 
     * preserving the host header when request URI changes
     *
     * @return void
     */
    public function testDoNotPreserveHostHeader()
    {
        $originalHost = $this->request->getHeaderLine('Host');
        $request = $this->request->withUri($this->request->getUri()->withHost('127.0.0.1'));
        
        $this->assertNotEquals($originalHost, $request->getHeaderLine('Host'));
        $this->assertEquals('127.0.0.1', $request->getHeaderLine('Host'));
    }

    public function testRequestTarget()
    {
        /**
         * check request target with URI path
         */
        $originalRequestTarget = $this->request->getRequestTarget();
        $this->assertEquals('/get/path?item=1', $originalRequestTarget);

        /**
         * check empty request target
         * 
         * Must return a string with single '/'
         */ 
        $request = $this->request->withRequestTarget('');
        $this->assertEquals('/', $request->getRequestTarget());
    }

    /**
     * Checks invalid request target
     *
     * @return void
     */
    public function testInvalidRequestTarget()
    {
        $this->expectException(\InvalidArgumentException::class);
        $request = $this->request->withRequestTarget('/path/inva lid/ ');
    }
    
    /**
     * Tests successful changing request method
     *
     * @return void
     */
    public function testChangeValidHttpMethod()
    {
        $this->assertEquals('GET', $this->request->getMethod());

        $request = $this->request->withMethod('HEAD');
        $this->assertEquals('HEAD', $request->getMethod());
    }
    
    /**
     * Tests error raised with an invalid method is passed to the request
     *
     * @return void
     */
    public function testInvalidHttpMethod()
    {
        $this->expectException(\InvalidArgumentException::class);

        $request = $this->request->withMethod('READ');
        $this->assertEquals('GET', $request->getMethod());
    }

    /**
     * Tests if the query string is correctly parsed
     *
     * @return void
     */
    public function testParseQueryStringFromUri()
    {
        $queryParams = $this->request->getQueryParams();
        $this->assertSame(['item' => '1'], $queryParams);
    }

    /**
     * Tests if the there's no query string in the request URI 
     *
     * @return void
     */
    public function testParseQueryStringFromServerParams()
    {
        $expected = [
            'server' => 'true',
            'item' => '2'
        ];

        $uri = $this->request->getUri()->withQuery('');
        $queryParams = $this->request->withUri($uri)->getQueryParams();
        
        $this->assertSame($expected, $queryParams);
    }

    /**
     * Tests change request instance query params
     *
     * @return void
     */
    public function testWithQueryParams()
    {
        $params = [
            'foo' => 'bar',
            'baz' => '001'
        ];
        $request = $this->request->withQueryParams($params);

        $this->assertSame($params, $request->getQueryParams());
    }

    /**
     * Tests if a json body is correctly parsed by the request
     *
     * @return void
     */
    public function testParseJsonBody()
    {
        $this->assertEquals('application/json', $this->request->getHeaderLine('Content-Type'));

        $parsedBody = $this->request->getParsedBody();
        $this->assertSame($this->jsonBody, $parsedBody);
    }

    public function testParseXmlBody()
    {
        /**
         * Creates a new request instance with XML body
         */
        $body = $this->request->getBody();
        $body->rewind();
        $body->write($this->xmlBody);
        $request = $this->request->withHeader('Content-Type', 'application/xml')->withBody($body);

        /**
         * XML Object
         */
        $xml = simplexml_load_string($this->xmlBody);
        
        /**
         * Asserts string xml body in request
         */
        $this->assertSame($this->xmlBody, $request->getBody()->getContents());
        
        /**
         * Asserts if the parsedbody is correctly XML parsed
         */
        $this->assertEquals($xml, $request->getParsedBody());

        /**
         * Asserts that the array form of both are the same
         */
        $this->assertSame(
            json_decode(json_encode($xml), true),
            json_decode(json_encode($request->getParsedBody()), true)
        );
    }

    /**
     * Tests if a request with form url encoded 
     * body will be parsed from $_POST
     *
     * @return void
     */
    public function testGetFormUrlEncoded()
    {
        $request = $this->request->withHeader(
            'Content-Type', 'application/x-www-form-urlencoded'
        );

        $this->assertSame($_POST, $request->getParsedBody());
    }

    public function testRequestAttributes()
    {
        $request = $this->request->withAttribute('stub', new StubClass());
        $request = $request->withAttribute('handler', function ($request) { 
            return 1;
        });

        /**
         * Assert instances of attributes of the request
         */
        $this->assertInstanceOf(StubClass::class, $request->getAttribute('stub'));
        $this->assertInstanceOf(\Closure::class, $request->getAttribute('handler'));

        /**
         * Assert default value of no existent attribute
         */
        $this->assertSame(null, $request->getAttribute('foo'));

        /**
         * Assert custom default value
         */
        $this->assertSame('bar', $request->getAttribute('baz', 'bar'));

        /**
         * Assert that the request without the attribute
         */
        $request = $request->withoutAttribute('handler');
        $this->assertSame(null, $request->getAttribute('handler'));
    }
}
