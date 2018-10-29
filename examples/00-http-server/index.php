<?php

use Framework\Http\Handlers\ErrorHandler;

use function Framework\Http\wrap;
use function Framework\Http\response;
use function Framework\Http\requestFromServerParams;

include '../../vendor/autoload.php';

$request = requestFromServerParams();

// Appends '+++' in response body
$middlewareAppendPlusInResponse = function ($request, $handler) {
    $response = $handler->handle($request);
    $response->getBody()->write('+++');
    return $response;
};

// Handle a request then return a response with code 200
// and the request URI path as content
$handler = function ($request) {
    return response(200, $request->getUri()->getPath());
};
$handler = wrap($handler, $middlewareAppendPlusInResponse);

// produce the response
try {
    $response = $handler->handle($request);
} catch (Throwable $error) {
    $response = (new ErrorHandler())->handle($request, $error);
}

// emits the response
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    header(sprintf('%s: %s', $name, implode(', ', $values)), true);
}
echo $response->getBody();
