<?php

namespace Framework\Core;

use Framework\Http\Uri;
use Framework\Http\Request;
use Framework\Http\UploadedFile;
use Framework\Container\Container;
use Framework\Router\RouteInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Http\Handlers\HasMiddlewareTrait;

class Application extends Container implements RequestHandlerInterface
{
    use HasMiddlwareTrait;

    /**
     * Array of the application settings
     *
     * @var array
     */
    protected $settings;

    /**
     * Creates a new application instance
     *
     * @param array $services
     */
    public function __construct(array $services)
    {
        $this->settings = $services['settings'] ?: [];

        parent::__construct($services);

        $this->registerDefaultServices();
        $this->bootServices();
    }

    /**
     * Executes the application
     *
     * @return void
     */
    public function execute(?ServerRequestInterface $request = null)
    {
        $response = $this->handle($request ?: $this->get('request'));
        $this->emitResponse($response);
    }

    /**
     * Handle the server request recieved and then
     * returns a response after middleware stack process.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            if ($this->hasMiddleware()) {
                $response = $this->processMiddleware($request);
            } elseif (($response = $this($request)) === null) {
                $response = $this->notFoundHandler->handle($request);
            }
        } catch (Exception $exception) {
            if (!$this->has('errorHandler')) {
                throw $exception;
            }
            $response = $this->errorHandler->handle($request, $exception);
        }

        return $response;
    }

    public function __invoke(ServerRequestInterface $request): ?ResponseInterface
    {
        $route = $request->getAttribute('route');
        if ($route === null) {
            $route = $this->router->match($request);
            $request = $request->withAttribute('route', $route);
        }

        if ($route->found()) {
            $response = $route->getHandler()->handle($request);
        } elseif ($route->notAllowed()) {
            $response = $this->notAllowedHandler->handle($request);
        } else {
            $response = $this->notFoundHandler->handle($request);
        }

        return $response;
    }

    /**
     * Emit an HTTP response.
     *
     * @param ResponseInterface $response
     * @return void
     */
    public function emitResponse(ResponseInterface $response)
    {
        $reasonPhrase = $response->getReasonPhrase();
        $statusCode   = $response->getStatusCode();
        header(sprintf(
            'HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $statusCode,
            ($reasonPhrase ? ' ' . $reasonPhrase : '')
        ), true, $statusCode);

        foreach ($response->getHeaders() as $name => $values) {
            $filtered = str_replace('-', ' ', $name);
            $filtered = ucwords($filtered);
            $name = str_replace(' ', '-', $filtered);

            header(sprintf('%s: %s', $name, implode(', ', $values)), true, $statusCode);
        }

        echo $response->getBody();
    }

    /**
     * Register all default services of the application if those have not been defined yet.
     *
     * @return void
     */
    private function registerDefaultServices()
    {
        /**
         * Register router
         */
        if (!$this->has('router')) {
            $this->register('router', function () {
                
                return new \Framework\Router\Router(
                    $this->settings['router']['type'] ?? 'simple', 
                    (array) $this->settings['router']['routesFile'],
                    $this->settings['router']['routesCacheFile'] ?? null
                );
            });
            $this->implemented('\Framework\Router\RouterInterface', '\Framework\Router\Router', true);
            $this->alias('router', '\Framework\Router\RouterInterface');
        }

        /**
         * Register the not found request handler
         */
        if (!$this->has('notFoundHandler')) {
            $this->register('\Framework\Http\Handlers\NotFoundHandler');
            $this->alias('notFoundHandler', '\Framework\Http\Handlers\NotFoundHandler');
        }
        
        /**
         * Register the request error handler
         */
        if (!$this->has('errorHandler')) {
            $this->implemented(
                '\Framework\Http\Handlers\ErrorRequestHandlerInterface',
                '\Framework\Http\Handlers\ErrorRequestHandler'
            );
            $this->alias(
                '\Framework\Http\Handlers\ErrorRequestHandler',
                '\Framework\Http\Handlers\ErrorRequestHandlerInterface'
            );
            $this->alias('errorHandler', '\Framework\Http\Handlers\ErrorRequestHandlerInterface');
        }
    }

    /**
     * Boot services that need initialization
     *
     * @return void
     */
    private function bootServices()
    {
        if (isset($this->settings['boot.services.file'])) {
            require $this->settings['boot.services.file'];
        }
    }
}
