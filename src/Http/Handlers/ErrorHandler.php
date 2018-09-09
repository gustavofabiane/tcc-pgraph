<?php

namespace Framework\Http\Handlers;

use Throwable;
use Framework\Http\Body;
use Framework\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Produces a default internal server error 
 * response for the given HTTP request.
 */
class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * Handle the request and return a response.
     *
     * @param ServerRequestInterface $request
     * @param Throwable $error
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, Throwable $error): ResponseInterface
    {
        $acceptHeader = $request->getHeader('Accept');
        $contentType = $acceptHeader ? $acceptHeader[0] : 'text/html';
        
        $contentTypeMethod = explode('/', $contentType)[1];
        
        if (method_exists($this, $contentTypeMethod)) {
            $content = $this->{$contentTypeMethod}($request, $error);
        } else {
            $content = $this->plain($request, $error);
            $contentType = 'text/plain';
        }

        $body = new Body();
        $body->write($content);

        return new Response(500, ['Content-Type' => $contentType], $body);
    }

    public function plain(ServerRequestInterface $request, Throwable $error)
    {
        return 'Resource not found';
    }

    public function json(ServerRequestInterface $request, Throwable $error)
    {
        return '{"message":"Resource not found"}';
    }
    
    public function xml(ServerRequestInterface $request, Throwable $error)
    {
        return '<root><message>Resource not found</message></root>';
    }

    public function html(ServerRequestInterface $request, Throwable $error)
    {
        return <<<END
<!DOCTYPE html>
<html>
    <head>
        <title>Page Not Found</title>
    </head>
    <body>
        <h1>Page Not Found</h1>
    </body>
</html>
END;
    }
}
