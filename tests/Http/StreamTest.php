<?php

namespace Tests\Http;

use Framework\Http\Stream;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    /**
     * Writable stream
     *
     * @var Stream
     */
    protected $writable;

    /**
     * readable stream
     *
     * @var Stream
     */
    protected $onlyReadable;

    public function setup()
    {
        $this->writable = new Stream(fopen(__DIR__ . '/../utils/writable-stream', 'w+'));
        $this->onlyReadable = new Stream(fopen(__DIR__ . '/../utils/readable-stream', 'r'));
    }

    public function tearDown()
    {
        $this->writable->close();
        $this->onlyReadable->close();
        
        // file_put_contents(__DIR__ . '/../utils/writable-stream', '');
    }

    public function testIsWritable()
    {
        $this->assertTrue($this->writable->isWritable());
        $this->assertFalse($this->onlyReadable->isWritable());
    }

    public function testWrite()
    {
        $this->writable->write('12345');
        $this->assertEquals('12345', (string) $this->writable);
        $this->assertEquals(5, $this->writable->tell());
        $this->writable->rewind();

        $this->writable->seek(1);
        $this->writable->write('321');
        $this->assertEquals('13215', (string) $this->writable);

        $this->writable->truncate(0);
        $this->assertEquals('', (string) $this->writable);
    }

    public function testWriteInOnlyReadable()
    {
        $this->expectException(\RuntimeException::class);
        $this->onlyReadable->write('test');
    }

    public function testSeek()
    {
        $this->assertTrue($this->onlyReadable->isSeekable());
        $this->onlyReadable->rewind();

        $this->onlyReadable->seek(9);
        $this->assertEquals('10111', $this->onlyReadable->read(5));

        $this->onlyReadable->seek(3, SEEK_CUR);
        $this->assertEquals('14151', $this->onlyReadable->read(5));

        $this->assertEquals('617181920', $this->onlyReadable->getContents());

        $this->assertEquals('1234567891011121314151617181920', (string) $this->onlyReadable);
    }
}