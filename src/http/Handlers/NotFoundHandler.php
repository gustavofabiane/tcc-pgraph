<?php

namespace Framework\Http\Handlers;

use Framework\Http\Body;
use Framework\Http\Response;
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
        
        $content = '';
        switch ($contentType) {
            case 'application/json':
                $content = $this->json($request);
                break;
            case 'text/xml':
            case 'application/xml':
                $content = $this->xml($request);
                break;
            case 'text/html':
                $content = $this->html($request);
                break;
            default:
                $content = $this->plain($request);
        }
        
        $notFoundBody = $request->getBody();
        $notFoundBody->truncate(0);
        $notFoundBody->write($content);
        
        $response = new Response();
        return $response->withStatus(404)
                        ->withHeader('Content-Type', $contentType)
                        ->withBody($notFoundBody);
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
