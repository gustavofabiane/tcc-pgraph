<?php

namespace Pgraph\Tests\Http;

use Pgraph\Http\Body;
use Pgraph\Http\Message;
use PHPUnit\Framework\TestCase;


class MessageTest extends TestCase
{
    /**
     * Message to be tested
     *
     * @var Message
     */
    protected $message;

    protected $testHeaders = [
        'CONTENT-TYPE' => ['application/json'],
        'Accept' => ['application/json'],
        'authorization' => ['Bearer TESTESTESTESTESTEST']
    ];

    public function setUp()
    {
        $this->message = new Message();
        $this->message = $this->message->withHeaders($this->testHeaders)
                                       ->withBody(new Body(__DIR__ . '/../utils/readable-stream', 'r'))
                                       ->withProtocolVersion('1.1');
    }

    public function testInvalidProtocolVersion()
    {
        $this->expectException(\InvalidArgumentException::class);

        $oldVersion = $this->message->getProtocolVersion();
        $message = $this->message->withProtocolVersion('2.0');
        $this->assertEquals($oldVersion, $message->getProtocolVersion());
    }

    public function testHeadersNameCaseInsensitive()
    {
        $this->isTrue($this->message->hasHeader('aCCEPT'));

        $header = $this->message->getHeader('CoNTent-TYPE');
        $this->assertEquals($this->testHeaders['CONTENT-TYPE'], $header);

        $headers = $this->message->getHeaders();
        $this->assertEquals($this->testHeaders, $headers);

        $testHeadersWithoutAuthorization = $this->testHeaders;
        unset($testHeadersWithoutAuthorization['authorization']);

        $message = $this->message->withoutHeader('AuthORIZATIOn');
        $this->assertEquals($testHeadersWithoutAuthorization, $message->getHeaders());
    }

    /**
     * Tests append header value feature of the Message implementation
     *
     * @depends testHeadersNameCaseInsensitive
     * @return void
     */
    public function testAppendHeaderValue()
    {
        $header = ['application/json', 'application/xml'];

        $message = $this->message->withAddedHeader('Accept', 'application/xml');
        $this->assertEquals($header, $message->getHeader('Accept'));

        $header[] = 'image/svg+xml'; 
        $header[] = 'text/html';
        $message = $message->withAddedHeader('Accept', ['image/svg+xml', 'text/html']);
        $this->assertEquals($header, $message->getHeader('Accept'));
    }

    public function testMessageBodyChange()
    {
        $newBody = new Body(fopen(__DIR__ . '/../utils/writable-stream', 'r+'));
        $newBody->write('test message implementation');

        $message = $this->message->withBody($newBody);
        $this->assertEquals($newBody, $message->getBody());

        $newBody->truncate(0);
    }
}
