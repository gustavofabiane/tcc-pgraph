<?php

namespace Framework\Http;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Representation of an outgoing, client-side request.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * During construction, implementations MUST attempt to set the Host header from
 * a provided URI if no Host header is provided.
 *
 * Requests are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
class Request extends Message implements ServerRequestInterface
{
    /**
     * The available and valid request methods
     */
    const HTTP_REQUEST_METHODS = [
        'CONNECT', 'DELETE', 'GET',
        'HEAD', 'OPTIONS', 'PATCH',
        'POST', 'PUT', 'TRACE'
    ];

    /**
     * The HTTP request method
     *
     * @var string
     */
    protected $method;

    /**
     * The request URI
     *
     * @var UriInterface
     */
    protected $uri;

    /**
     * The request target
     *
     * @var string
     */
    protected $requestTarget;

    /**
     * The associative array of server parameters
     * of the request
     *
     * @var array
     */
    protected $serverParams;

    /**
     * The request parsed query params
     *
     * @var array
     */
    protected $queryParams;

    /**
     * The server request cookies
     *
     * @var array
     */
    protected $cookies;

    /**
     * Registered handlers to parse body data to a PHP readable form
     *
     * @var array
     */
    protected $bodyParsers = [];

    /**
     * The request body parsed to array or object
     *
     * @var mixed
     */
    protected $parsedBody;

    /**
     * The request attributes
     *
     * @var array
     */
    protected $attributes = [];

    public function __construct(
        string $method,
        array $serverParams,
        array $headers,
        array $cookies,
        StreamInterface $body,
        array $bodyParsers = []
    ) {
        if (!$this->isValidHttpMethod($method)) {
            throw new \InvalidArgumentException($method . ' is not a valid HTTP method');
        }
        $this->method = $method;
        $this->serverParams = $serverParams;
        $this->cookies = $cookies;
        $this->body = $body;

        foreach ($headers as $name => $value) {
            $normalizedName = $this->normalizeHeaderName($name);
            $normalizedValue = is_array($value) ? $value : explode(',', $value);
            $this->originalHeadersNames[$normalizedName] = $name;
            $this->headers[$normalizedName] = array_map(
                [$this, 'normalizeHeaderValue'],
                $normalizedValue
            );
        }

        $this->bodyParsers = [
            'multipart/form-data' => [$this, 'parsedBodyFromGlobal'],
            'application/x-www-form-urlencoded' => [$this, 'parsedBodyFromGlobal'],
        ];

        foreach ($bodyParsers as $contentType => $parser) {
            if (!is_callable($parser)) {
                throw new \InvalidArgumentException(
                    'The given parser for \'' . $contentType . '\' is not a valid callable'
                );
            }
            $this->bodyParsers[$contentType] = $parser;
        }
    }
    
    /**
     * This method is applied to the cloned object
     * after PHP performs an initial shallow-copy. This
     * method completes a deep-copy by creating new objects
     * for the cloned object's internal reference pointers.
     *
     * Note: Originally extracted from SlimFramwork
     * Note: This method is not part of the PSR-7 specification.
     */
    public function __clone()
    {
        $this->uri = clone $this->uri;
        $this->body = clone $this->body;
    }
    
    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget()
    {
        if (!$this->requestTarget && $this->uri) {
            $target = $this->uri->getPath();
            $query = $this->uri->getQuery();
            if ($query) {
                $target .= '?' . $query;
            }
            $this->requestTarget = $target;
        }
        return $this->requestTarget ?: '/';
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-5.3 (for the various
     *     request-target forms allowed in request messages)
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new \InvalidArgumentException(
                'The request target must be a string and cannot contain whitespace'
            );
        }
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Verifies if the given parameter is a valid HTTP method
     *
     * Note: This method is not part of the PSR-7 specifivation.
     *
     * @param mixed $method
     * @return boolean
     */
    public function isValidHttpMethod($method)
    {
        return is_string($method) && in_array(strtoupper($method), static::HTTP_REQUEST_METHODS);
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     * @return static
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
        if (!$this->isValidHttpMethod($method)) {
            throw new \InvalidArgumentException($method . ' is not a valid HTTP method');
        }
        $clone = clone $this;
        $clone->method = $method;
        
        return $clone;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;

        $uriHost = $uri->getHost();
        if ($preserveHost) {
            if ($uriHost && !$clone->hasHeader('Host')) {
                $clone = $clone->withHeader('Host', $uriHost);
            }
        } elseif ($uriHost) {
            $clone = $clone->withHeader('Host', $uriHost);
        } else {
            $clone = $clone->withoutHeader('Host');
        }

        return $clone;
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookies;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookies = $cookies;
        
        return $clone;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams()
    {
        if (!$this->queryParams) {
            $this->queryParams = [];
            $query = $this->uri->getQuery();
            parse_str($query, $queryParams);
        }

        return $this->queryParams;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *     $_GET.
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->querParams = $query;
        
        return $clone;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles()
    {
        throw new \Exception('Not implemented yet.');
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        throw new \Exception('Not implemented yet.');
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody()
    {
        if (!$this->parsedBody) {
            $contentType = $this->getHeader('Content-Type')[0];
            $parser = $this->bodyParsers[$contentType] ?? null;
            if (!is_null($parser)) {
                if ($this->body->isSeekable()) {
                    $this->body->rewind();
                }
                $this->parsedBody = call_user_func($parser, $this->body->getContents());
            }
        }
        
        return $this->parsedBody;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *     typically be in an array or object.
     * @return static
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function withParsedBody($data)
    {
        if (!is_null($data) && !is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException('Invalid body parsed data passed to request');
        }
        $clone = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes($name) ?? $default;
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        
        return $clone;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @return static
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        
        return $clone;
    }

    /**
     * Helper method to get the request parsed body from the PHP super global $_POST.
     *
     * Note: This method is not part of the PSR-7 specification.
     *
     * @param mixed $content
     * @return array
     */
    protected function parsedBodyFromGlobal($content = null)
    {
        return $_POST;
    }

    /**
     * Returns an instance with the given content type body parser.
     * 
     * Note: This method is not part of the PSR-7 specification.
     *
     * @param string $contentType
     * @param callable $parser
     * @return static
     */
    public function withBodyParser($contentType, callable $parser)
    {
        $clone = clone $this;
        $clone[$contentType] = $parser;

        return $clone;
    }
}
