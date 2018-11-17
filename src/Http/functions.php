<?php

declare(strict_types=1);

namespace Pgraph\Http;

use Pgraph\Http\Stream;
use Pgraph\Http\Response;
use Pgraph\Core\Application;
use Psr\Http\Message\StreamInterface;
use Pgraph\Http\ResponseStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Pgraph\Http\Handlers\HandlerWrapper;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Create a PSR-7 request implementation instance from server params.
 *
 * @return ServerRequestInterface
 */
function requestFromServerParams(): ServerRequestInterface
{
    $request = new Request(
        $_SERVER['REQUEST_METHOD'], $_SERVER,
        Uri::createFromServerParams($_SERVER),
        [], [], 
        new Body('php://input', 'r'), 
        UploadedFile::filterNativeUploadedFiles($_FILES ?: [])
    );
    if (!empty($_POST)) {
        $request = $request->withParsedBody($_POST);
    }
    return $request;
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
    
    /** @var \Psr\Http\Message\ResponseInterface $response */
    $response = Application::getInstance()
        ->get('response')
        ->withStatus($statusCode)
        ->withBody($body);
    
    foreach ($headers as $name => $value) {
        $response = $response->withHeader($name, $value);
    }

    return $response;
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
    return Application::getInstance()->get('response')->withJson($data, $statusCode);
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
