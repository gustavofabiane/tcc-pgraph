<?php

namespace Framework\Router;

use function Framework\isImplementerOf;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Http\Handlers\ResolvableRequestHandler;

/**
 * Handler for route callback execution
 */
class RouteRequestHandler extends ResolvableRequestHandler
{
    /**
     * The route handled
     *
     * @var RouteInterface
     */
    protected $route;

    /**
     * Handle the server request recieved and then
     * returns a response after middleware stack process
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->route->found()) {
            throw new \RuntimeException(
                sprintf('Unable to handle route for \'%s\'', $this->route->getPath())
            );
        }

        if ($this->hasMiddleware()) {
            return $this->processMiddleware($request);
        }

        if (isImplementerOf($this->resolvable, RequestHandlerInterface::class)) {
            $this->resolvable = [$this->resolvable, 'handle'];
        }

        $queryParams = $request->getQueryParams() ?: [];
        $parameters = [
            'request' => $request,
            'params'  => $queryParams,
            'args'    => $this->route->getArguments()
        ];
        $parameters += $queryParams + $this->route->getArguments();

        return $this->container->resolve($this->resolvable, $parameters);
    }

    /**
     * Set the route that will be handled
     *
     * @param RouteInterface $route
     * @return static
     */
    public function setRoute(RouteInterface $route)
    {
        $this->route = $route;
        return $this;
    }
}
