<?php

namespace Framework\Http;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\MessageInterface;

/**
 * HTTP Message implementation
 *
 * @link http://www.ietf.org/rfc/rfc7230.txt
 * @link http://www.ietf.org/rfc/rfc7231.txt
 */
class Message implements MessageInterface
{
    /**
     * The valid HTTP protocol versions
     */
    const HTTP_VERSIONS = ['1.0', '1.1'];

    /**
     * HTTP message protocol version
     *
     * @var string
     */ 
    protected $protocolVersion;

    /**
     * The HTTP Message headers
     *
     * @var array
     */
    protected $headers;

    /**
     * The original headers key value.
     *
     * Where key is the normalized header and the value is the original one.
     * 
     * @var array
     */
    protected $originalHeadersNames;

    /**
     * The HTTP message body
     *
     * @var StreamInterface
     */
    protected $body;

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    public function isValidProtocolVersion($version)
    {
        return is_string($version) && in_array($version, static::HTTP_VERSIONS);
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        if (!$this->isValidProtocolVersion($version)) {
            throw new \InvalidArgumentException($version . ' is not a valid version of the HTTP protocol');
        }
        $clone = clone $this;
        $clone->protocolVersion = $version;
        
        return $clone;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders()
    {
        $headers = [];
        foreach ($this->headers as $header => $value) {
            $headers[$this->originalHeadersNames[$header]] = $value;
        }
        return $headers;
    }

    protected function normalizeHeaderName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('"' . $name . '" is not a valid header name');
        }
        $name = str_replace(['-', '_'], ' ', trim($name));
        $normalized = str_replace(' ', '-', ucwords(mb_strtolower($name)));
        
        return $normalized;
    }

    protected function normalizeHeaderValue($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('"' . $value  . '" is not a valid header value');
        }
        return trim($value);
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        return isset($this->headers[$this->normalizeHeaderName($name)]);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name)
    {
        return $this->headers[$this->normalizeHeaderName($name)] ?? [];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name)
    {
        if (!$this->hasHeader($name)) {
            return '';
        }
        return implode(', ', $this->headers[$this->normalizeHeaderName($name)]);
    }

    public function withHeaders(array $headers, bool $appendValues = false)
    {
        $newHeaders = $originalNames = [];
        foreach ($headers as $name => $value) {
            $normalizedName = $this->normalizeHeaderName($name);
            $value = is_array($value) ? $value : explode(',', $value);
            $values = array_map([$this, 'normalizeHeaderValue'], $value);
            if ($appendValues && isset($this->headers[$normalizedName])) {
                $values = array_unique(
                    array_merge($this->headers[$normalizedName], $values)
                );
            }
            $newHeaders[$normalizedName] = $values;
            $originalNames[$normalizedName] = $name;
        }
        $clone = clone $this;
        $clone->headers = $newHeaders;
        $clone->originalHeadersNames = $originalNames;

        return $clone;
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value)
    {
        return $this->withHeaders([$name => $value]);
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value)
    {
        return $this->withHeaders([$name => $value], true);
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return static
     */
    public function withoutHeader($name)
    {
        $name = $this->normalizeHeaderName($name);

        $clone = clone $this;
        unset($clone->headers[$name]);
        unset($clone->originalHeadersNames[$name]);
        
        return $clone;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     * @return static
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }
}
