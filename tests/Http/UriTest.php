<?php

namespace Framework\Tests\Http;

use Framework\Http\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    protected $uriString = 'http://domain.com:8080/path/to/tests?test=ok#begin';
    protected $uriStringFull = 'https://john:goodpass@domain.com:443/path/to/tests?query=string&param=2#good-fragment';

    /**
     * URI to test
     *
     * @var Uri
     */
    protected $uri;

    /**
     * A URI with all its possible components
     *
     * @var Uri
     */
    protected $fullUri;

    public function setup()
    {
        $this->uri = Uri::createFromString($this->uriString);
        $this->fullUri = Uri::createFromString($this->uriStringFull);
    }

    protected function uriFromString($string)
    {
        return Uri::createFromString($string);
    }

    public function testCreateFromString()
    {
        $uriFromString = Uri::createFromString($this->uriString);
        $this->assertInstanceOf(Uri::class, $uriFromString);

        $this->assertEquals('http', $uriFromString->getScheme());
        $this->assertEquals(8080, $uriFromString->getPort());
    }

    public function testUriToString()
    {
        $this->assertEquals($this->uriString, (string) $this->uri);
        $this->assertEquals($this->uriStringFull, (string) $this->fullUri);
    }

    public function testChangeQuery()
    {
        $query = 'query=test with percent encoded';
        $withQuery = $this->uri->withQuery('?' . $query);
        $this->assertEquals(
            preg_replace_callback(
                '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
                function ($match) {
                    return rawurlencode($match[0]);
                },
                $query
            ), 
            $withQuery->getQuery());
    }

    public function testRemovePort()
    {
        $this->assertInternalType('int', $this->uri->getPort());
        
        $uri = $this->uri->withPort(null);
        $this->assertNull($uri->getPort());
    }

    public function testInvalidPort()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $oldPort = $this->uri->getPort();
        $uri = $this->uri->withPort(999999);
        $this->assertEquals($oldPort, $uri->getPort());
    }

    public function testUriWithNoPath()
    {
        $string = 'http://domain.com?query=string';
        $uri = $this->uriFromString($string);
        
        $this->assertInternalType('string', $uri->getPath());
        $this->assertEquals('', $uri->getPath());
        $this->assertEquals($string, (string) $uri);
    }
    
    public function testDoNotNormalizeSingleSlashPath()
    {
        $stringSlash = 'http://domain.com/?query=string';
        $uri = $this->uriFromString($stringSlash);

        $this->assertEquals($stringSlash, (string) $uri);
        $this->assertEquals('/', $uri->getPath());
        
        $this->assertEquals('', (string) $uri->withPath('')->getPath());
    }
}
