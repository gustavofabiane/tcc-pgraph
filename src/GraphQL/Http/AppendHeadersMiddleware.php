<?php

declare(strict_types=1);

namespace Framework\GraphQL\Http;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Append defined headers to a response instance.
 */
class AppendHeadersMiddleware implements MiddlewareInterface
{
    /**
     * Headers to append in response
     *
     * @var array
     */
    protected $headers;

    /**
     * Create a new instance of AppendHeadersMiddleware.
     *
     * @param array $headers
     */
    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Append the headers to the response instance after request is handled.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        foreach ($this->headers as $name => $value) {
            $response = $response->withAddedHeader($name, $value);
        }
        return $response;
    }
}
