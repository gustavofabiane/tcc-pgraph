<?php

namespace Pgraph\Http\Handlers;

use Throwable;
use Pgraph\Http\Body;
use Pgraph\Http\Response;
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
        $content = 'An error has ocurred' . PHP_EOL;
        $content .= 'Message: ' . $error->getMessage() . PHP_EOL;
        $content .= 'Previous: ' . ($error->getPrevious() ? $error->getPrevious()->getMessage() . ', ' . $error->getPrevious()->getFile() . ' - ' . $error->getPrevious()->getLine() : '') . PHP_EOL;
        $content .= $error->getTraceAsString();

        return $content;
    }

    public function json(ServerRequestInterface $request, Throwable $error)
    {
        return '{"message":"An error has ocurred"}';
    }
    
    public function xml(ServerRequestInterface $request, Throwable $error)
    {
        return '<root><message>An error has ocurred</message></root>';
    }

    public function html(ServerRequestInterface $request, Throwable $error)
    {
        $class = get_class($error);
        $code = $error->getCode();
        $file = $error->getFile();
        $line = $error->getLine();
        $message = $error->getMessage();
        $previousMessage = $error->getPrevious() ? $error->getPrevious()->getMessage() : '';
        $stackTrace = $error->getTraceAsString();

        return <<<END
<!DOCTYPE html>
<html>
    <head>
        <title>Error</title>
        <style>* { font-family: Arial, sans-serif; }</style>
    </head>
    <body>
        <h1>Error</h1>
        <table>
            <tr><td>Error:</td><td>$class</td></tr>
            <tr><td>Message:</td><td>$message</td></tr>
            <tr><td>Previous:</td><td>$previousMessage</td></tr>
            <tr><td>Code:</td><td>$code</td></tr>
            <tr><td>File:</td><td>$file</td></tr>
            <tr><td>Line:</td><td>$line</td></tr>
        </table>
        <pre style="font-family: monospace; font-size: 14px">$stackTrace</pre>
    </body>
</html>
END;
    }
}
