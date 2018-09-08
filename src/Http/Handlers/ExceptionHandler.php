<?php

namespace Framework\Http\Handlers;

use Exception;
use Framework\Http\Body;
use Framework\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Produces a default not found response for the given http request
 */
class ExceptionHandler implements RequestErrorHandlerInterface
{
    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request, Exception $exception): ResponseInterface
    {
        $acceptHeader = $request->getHeader('Accept');
        $contentType = $acceptHeader ? $acceptHeader[0] : 'text/html';
        
        $contentTypeMethod = explode('/', $contentType)[1];
        
        if (method_exists($this, $contentTypeMethod)) {
            $content = $this->{$contentTypeMethod}($request);
        } else {
            $content = $this->plain($request);
            $contentType = 'text/plain';
        }

        $body = new Body();
        $body->write($content);

        return new Response(500, ['Content-Type' => $contentType], $body);
    }

    public function plain(ServerRequestInterface $request)
    {
        return 'Resource not found';
    }

    public function json(ServerRequestInterface $request)
    {
        return '{"message":"Resource not found"}';
    }
    
    public function xml(ServerRequestInterface $request)
    {
        return '<root><message>Resource not found</message></root>';
    }

    public function html(ServerRequestInterface $request)
    {
        return <<<END
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
