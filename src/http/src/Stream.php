<?php

namespace Framework\Http;

use Psr\Http\Message\StreamInterface;

/**
 * Implements PSR-7's StreamInterface
 */
class Stream implements StreamInterface
{   
    /**
     * The stream resource
     *
     * @var resource
     */
    private $stream;

    /**
     * The stream size
     *
     * @var int
     */
    private $size;

    /**
     * If stream is writable
     *
     * @var bool
     */
    private $writable;

    /**
     * If stream is readable
     *
     * @var bool
     */
    private $readable;

    /**
     * If the stream is seekable
     *
     * @var bool
     */
    private $seekable;

    /**
     * Stream's metadata
     *
     * @var array
     */
    private $metadata;

    /**
     * Accessing mode of the stream
     *
     * @var string
     */
    private $mode;

    /**
     * Available modes for stream
     *
     * @var array
     */
    protected $modes = [
        'r', 'r+', 'w', 'w+',
        'a', 'a+', 'x', 'x+'
    ];

    /**
     * Readable modes
     *
     * @var array
     */
    protected $readableModes = ['r', 'r+', 'w+', 'a+', 'x+'];

    /**
     * Writable modes
     *
     * @var array
     */
    protected $writableModes = ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+'];

    /**
     * Creates a new stream object
     *
     * @param resource|string $stream
     * @param string $mode
     */
    public function __construct($stream = 'php://temp', string $mode = 'r')
    {
        if(!is_resource($stream) && file_exists($stream) && ($stream = fopen($stream, $mode) === false)) {
            throw new \InvalidArgumentException($stream . ' is not a valid stream path');
        }
        $this->stream = $stream;
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString()
    {
        $this->rewind();
        return $this->getContents();
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        fclose($this->stream);
        $this->detach();
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $stream = $this->stream;
        $this->stream = null;
        $this->size = null;
        $this->writable = null;
        $this->readable = null;
        $this->seekable = null;
        $this->metadata = null;
        return $stream;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        if (!$this->size && is_resource($this->stream)) {
            $stats = fstat($this->stream);
            if (isset($stats['size'])) {
                $this->size = $stats['size'];
            }
        }
        return $this->size;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
        if (!is_resource($this->stream) || ($position = ftell($this->stream)) === false) {
            throw new \RuntimeException('Cannot tell stream\'s pointer current position');
        }
        return $position;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        return is_resource($this->stream) ? feof($this->stream) : true;
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        if (!$this->seekable && is_resource($this->stream)) {
            $meta = $this->getMetadata();
            $this->seekable = isset($meta['seekable']) && $meta['seekable'];
        }
        return $this->seekable;
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!is_resource($this->stream) || !$this->isSeekable() || fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Cannot seek stream position');
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
        if (!is_resource($this->stream) || !$this->isSeekable() || rewind($this->stream) === false) {
            throw new \RuntimeException('Cannot rewind stream pointer');
        };
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        if(!$this->writable && is_resource($this->stream)) {
            $mode = $this->getMetadata('mode');
            $this->writable = in_array($mode, $this->writableModes);
        }
        return $this->writable;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string)
    {
        if (!is_resource($this->stream) || !$this->isWritable() || ($written = fwrite($this->stream, $string)) === false) {
            throw new \RuntimeException('Cannot write in stream');
        }
        $this->size = null;
        return $written;
    }

    /**
     * Truncates the stream to the given size
     * 
     * Note: This method is not specified by PSR-7
     *
     * @param int $size The size to the stream to be truncated
     * @return void
     * @throws \RuntimeException on failure
     */
    public function truncate($size)
    {
        if (!is_resource($this->stream) || !$this->isWritable() || ftruncate($this->stream, $size) === false) {
            throw new \RuntimeException('Cannot write in stream');
        }
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        if(!$this->readable && is_resource($this->stream)) {
            $mode = $this->getMetadata('mode');
            $this->readable = in_array($mode, $this->readableModes);
        }
        return $this->readableModes;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
        if (!is_resource($this->stream) || !$this->isReadable() || ($content = fread($this->stream, $length)) === false) {
            throw new \RuntimeException('Cannot get stream contents');
        }
        return $content;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents()
    {
        if (!is_resource($this->stream) || !$this->isReadable() || ($content = stream_get_contents($this->stream)) === false) {
            throw new \RuntimeException('Cannot get stream contents');
        }
        return $content;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        $this->metadata = stream_get_meta_data($this->stream);
        if ($key) {
            return $this->metadata[$key] ?? null;
        }
        return $this->metadata;
    }
}
