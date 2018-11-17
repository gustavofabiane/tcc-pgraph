<?php

namespace Pgraph\Tests\Http;

use Pgraph\Http\Response;
use PHPUnit\Framework\TestCase;
use Pgraph\Tests\Stubs\StubClass;
use Pgraph\Http\ResponseStatusCode;

class ResponseTest extends TestCase
{
    /**
     * Base test response
     *
     * @var Response
     */
    protected $response;

    public function setUp()
    {
        $this->response = new Response();
    }

    public function testWithStatusCode()
    {
        $this->assertSame(ResponseStatusCode::OK, $this->response->getStatusCode());
        $this->assertEquals(
            ResponseStatusCode::STATUS_REASON_PHRASES[ResponseStatusCode::OK], 
            $this->response->getReasonPhrase()
        );
        
        $response = $this->response->withStatus(400);
        
        $this->assertSame(ResponseStatusCode::BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(
            ResponseStatusCode::STATUS_REASON_PHRASES[ResponseStatusCode::BAD_REQUEST], 
            $response->getReasonPhrase()
        );
        
        $customReason = 'This is a custom reason';
        $response = $response->withStatus(201, $customReason);
        
        $this->assertSame(ResponseStatusCode::CREATED, $response->getStatusCode());
        $this->assertEquals($customReason, $response->getReasonPhrase());
    }

    public function testJsonBody()
    {
        $data = [
            'json' => true,
            'data' => [1, 2, 3, 5],
            'more' => 'data'
        ];
        $response = $this->response->withJson($data);
        
        $parsedJson = json_decode($response->getBody()->getContents(), true);
        $this->assertSame($data, $parsedJson);
        $this->assertEquals('application/json;charset=utf-8', $response->getHeaderLine('Content-Type'));
    }
}
