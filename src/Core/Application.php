<?php

namespace Framework\Core;

use Throwable;
use Framework\Container\Container;
use Framework\Router\RouteInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Console\Exception\LogicException;

use function Framework\isImplementerOf;

class Application extends Container implements RequestHandlerInterface
{
    /**
     * App configuration container.
     *
     * @var Configuration
     */
    public $config;

    /**
     * List of deffered services from providers that were not loaded yet.
     *
     * @var array
     */
    protected $deferredServices = [];

    /**
     * An array of MiddlewareInterface to be
     * proccessed by the handler.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Creates a new application instance.
     *
     * @param Configuration $configuration
     * @param array $services
     */
    public function __construct(Configuration $config = null, array $services = [])
    {
        parent::__construct($services);

        $this->config = $config ?: Configuration::create([
            'prefix' => 'config'
        ]);
        $this->config->setApplication($this);
        $this->register('config', $this->config);
    }

    /**
     * Register application default provider.
     *
     * @return void
     */
    public function registerDefaultProvider(): void
    {
        (new DefaultProvider())->provide($this);
    }

    /**
     * Provide dependencies for application.
     *
     * @param string $provider
     * @param array $services
     * @return void
     */
    public function addProvider(string $provider, array $services = null): void
    {
        if (!isImplementerOf($provider, ProviderInterface::class)) {
            throw new \InvalidArgumentException(sprintf(
                'Parameter $provider must be a classname that ' . 
                'implements ProviderInterface, %s given',
                $provider
            ));
        }

        if (is_array($services)) {
            foreach($services as $service) {
                $this->deferredServices[$service] = $provider;
            }
        } else {
            if (is_string($provider)) {
                $provider = new $provider();
            }
            $provider->provide($this);
        }
    }

    /**
     * Try to find entry in deffered providers.
     *
     * @param string $id
     * @return void
     */
    public function findInDefferedProviders(string $id): void
    {
        if (array_key_exists($id, $this->deferredServices)) {
            $provider = $this->deferredServices[$id];
            (new $provider())->provide($this);

            unset($this->deferredServices[$id]);
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
    public function run(?ServerRequestInterface $request = null): void
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
            // Check if the route must be defined before application middleware be executed
            if ($this->config->get('app', 'define_route_before_middleware') && 
                !$request->getAttribute('route')
            ) {
                $request = $this->defineRequestRoute($request);

                /** @var \Framework\Router\RouteInterface $route */
                $route = $request->getAttribute('route');

                // We are going to ignore middleware if the route does not exists
                if ($route->isNotAllowed()) {
                    return $this->notAllowedHandler->handle($request);
                } elseif (!$route->isFound()) {
                    return $this->notFoundHandler->handle($request);
                }
            }

            if ($this->hasMiddleware()) {
                // Call the middleware stack
                $response = $this->processMiddleware($request);
            } else {
                // Execute the route middleware stack and its handler
                $response = $this->callRoute($request);
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
     * @return ResponseInterface
     */
    protected function callRoute(ServerRequestInterface $request): ResponseInterface
    {
        /** @var \Framework\Router\RouteInterface $route */
        $route = $request->getAttribute('route');

        if ($route === null) {
            $request = $this->defineRequestRoute($request);
            $route = $request->getAttribute('route');
        }

        if ($route->isFound()) {
            $response = $this->routeHandler->route($route)->handle($request);
        } elseif ($route->isNotAllowed()) {
            $response = $this->notAllowedHandler->handle($request, $route->getMethods());
        } else {
            $response = $this->notFoundHandler->handle($request);
        }

        return $response;
    }

    /**
     * Define the request application route.
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
     * Get the middleware stack.
     *
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Checks if the handler has middleware in its stack.
     *
     * @return bool
     */
    public function hasMiddleware(): bool
    {
        return !empty($this->middleware);
    }

    /**
     * Process the application middleware stack.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function processMiddleware(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = array_shift($this->middleware);

        if ($middleware instanceof \Closure) {
            $middleware = $middleware->bindTo($this);
        } elseif (isImplementerOf(MiddlewareInterface::class, $middleware)) {
            if (!is_object($middleware)) {
                $middleware = $this->resolve($middleware);
            }
            $middleware = [$middleware, 'process'];
        }

        return call_user_func(
            $middleware, $request, $this
        );
    }

    /**
     * Add a middleware definition to the route instance.
     *
     * @param callacle|\Psr\Http\Server\MiddlewareInterface $middleware
     * @return static
     */
    public function add($middleware): self
    {
        array_unshift($this->middleware, $middleware);
        return $this;
    }

    /**
     * Add a list of middleware.
     *
     * @see add()
     *
     * @param array $middlewareGroup
     * @return static
     */
    public function middleware(array $middlewareGroup): self
    {
        foreach ($middlewareGroup as $middleware) {
            $this->add($middleware);
        }
        return $this;
    }
    
    /**
     * Emit an HTTP response.
     *
     * @param ResponseInterface $response
     * @return void
     */
    public function emitResponse(ResponseInterface $response): void
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
