<?php

namespace Pgraph\Http\Handlers;

use Pgraph\Http\Body;
use Pgraph\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Produces a default not found response for the given http request
 */
class NotFoundHandler implements RequestHandlerInterface
{
    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
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

        return new Response(404, ['Content-Type' => $contentType], $body);
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
