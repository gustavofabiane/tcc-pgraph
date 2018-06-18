<?php

namespace Framework\Tests\Http;

use Framework\Http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    protected $request;

    public function setUp()
    {
        $this->request = new Request('GET', [
            'HTTPS' => 'On',
            'HTTP_HOST' => 'localhost:8080',
            'CONTENT_TYPE' => 'application/json',
            'REQUEST_URI' => '/test/1'
        ]);
    }
}
