<?php

declare(strict_types=1);

namespace Framework\Http;

use Framework\Http\Stream;
use Framework\Http\Response;
use Psr\Http\Message\StreamInterface;
use Framework\Http\ResponseStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Framework\Http\Handlers\HandlerWrapper;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Create a PSR-7 request implementation instance from server params.
 *
 * @return ServerRequestInterface
 */
function requestFromServerParams(): ServerRequestInterface
{
    return new Request(
        $_SERVER['REQUEST_METHOD'], $_SERVER,
        Uri::createFromServerParams($_SERVER),
        [], [], 
        new Body('php://input', 'r'), 
        UploadedFile::filterNativeUploadedFiles($_FILES ?: [])
    );
}

/**
 * Create a PSR-7 compliant HTTP Response instance.
 *
 * @param int $statusCode
 * @param StreamInterface|string $body
 * @param array $headers
 * @return ResponseInterface
 */
function response(
    int $statusCode = ResponseStatusCode::OK,
    $body = null,
    array $headers = []
): ResponseInterface {
    if (! $body instanceof StreamInterface) {
        $content = $body;
        $body = new Stream();
        $body->write($content);
    }
    return new Response($statusCode, $headers, $body);
}

/**
 * Create a PSR-7 compliant HTTP Response with serialized JSON body.
 *
 * @param array|object|\JsonSerializable $data
 * @param int $statusCode
 * @return ResponseInterface
 */
function jsonResponse($data, int $statusCode = ResponseStatusCode::OK): ResponseInterface
{
    return (new Response())->withJson($data, $statusCode);
}

/**
 * Wrap a request handler process with middleware/callable
 *
 * @param RequestHandlerInterface|callable $handler
 * @param MiddlewareInterface|callable $middleware
 * @return HandlerWrapper
 */
function wrap($handler, $middleware): HandlerWrapper 
{
    if (!$handler instanceof HandlerWrapper) {
        $handler = new HandlerWrapper($handler);
    }
    return $handler->add($middleware);
}
