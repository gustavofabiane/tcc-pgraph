<?php

namespace Framework\Core;

use Throwable;
use Framework\Container\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Http\Handlers\HasMiddlewareTrait;

class Application extends Container implements RequestHandlerInterface
{
    use HasMiddlewareTrait;

    /**
     * App configuration container.
     *
     * @var Configuration
     */
    public $config;

    /**
     * List of deffered service providers that were not loaded yet.
     *
     * @var array
     */
    protected $providers = [];

    /**
     * Creates a new application instance.
     *
     * @param array $services
     */
    public function __construct(array $services, Configuration $config = null)
    {
        parent::__construct($services);

        $this->config = $config ?: Configuration::create([
            'prefix' => 'config'
        ]);
        
        static::setInstance($this);
        
        (new DefaultProvider())->provide($this);
    }

    /**
     * Provide dependencies for application.
     *
     * @param string|ProviderInterface $provider
     * @return void
     */
    public function provide($provider, array $services = null)
    {
        if (!is_null($provide)) {
            $this->providers[] = compact('provider', 'services');
        } elseif (! $provider instanceof ProviderInterface) {
            $provider = $this->resolve($provider);
        }
        $provider->provide($this);
    }

    /**
     * Try to find entry in deffered providers.
     *
     * @param string $id
     * @return void
     */
    public function findInDefferedProviders(string $id)
    {
        foreach ($this->providers as $key => &$provider) {
            if (in_array($id, $provider['services'])) {
                $provider = $provider['provider'] instanceof ProviderInterface 
                    ? $provider['provider'] 
                    : $this->resolve($provider['provider']);
                $provider->provide($this);
                unset($this->providers[$key]);
                break;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if ($this->config->isConfiguration($id)) {
            return $this->config->get($id);
        }
        if (!$this->has($id)) {
            $this->findInDefferedProviders($id);
        }
        return parent::get($id);
    }

    /**
     * Executes the application.
     *
     * @return void
     */
    public function run(?ServerRequestInterface $request = null)
    {
        $this->emitResponse(
            $this->handle($request ?: $this->get('request'))
        );
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
            if ($this->defineRouteBeforeMiddleware && !$request->getAttribute('route')) {
                $request = $this->defineRequestRoute($request);
            }
            if ($this->hasMiddleware()) {
                $response = $this->processMiddleware($request);
            } elseif (($response = $this->callRouteHandler($request)) === null) {
                $response = $this->notFoundHandler->handle($request);
            }
        } catch (Throwable $error) {
            if (!$this->has('errorHandler')) {
                throw $error;
            }
            $response = $this->errorHandler->handle($request, $error);
        }

        return $response;
    }

    /**
     * Call the request route handler.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface|null
     */
    protected function callRouteHandler(ServerRequestInterface $request): ?ResponseInterface
    {
        $route = $request->getAttribute('route');
        if ($route === null) {
            $request = $this->defineRequestRoute($request);
            $route = $request->getAttribute('route');
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
     * Define the request route
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function defineRequestRoute(ServerRequestInterface $request): ServerRequestInterface
    {
        $route = $this->router->match($request);
        foreach ($route->getArguments() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }
        return $request->withAttribute('route', $route);
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

            $first = stripos($name, 'Set-Cookie') === 0 ? false : true;
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), $first);
                $first = false;
            }
        }

        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }
        while (!$body->eof()) {
            echo $body->read(2048);
            if (connection_status() != CONNECTION_NORMAL) {
                break;
            }
        }
    }
}
