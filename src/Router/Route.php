<?php

namespace Framework\Router;

use FastRoute\Dispatcher;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Http\Handlers\HasMiddlewareTrait;

class Route implements RouteInterface
{
    /**
     * The route request handler.
     *
     * @var RequestHandlerInterface
     */
    protected $handler;

    /**
     * The route status.
     *
     * @var int
     */
    protected $status;

    /**
     * The route arguments
     *
     * @var array
     */
    protected $arguments;

    /**
     * Creates a new route instance.
     *
     * @param RequestHandlerInterface $handler
     */
    public function __construct(
        int $status,
        ?RequestHandlerInterface $handler = null,
        ?array $arguments = []
    ) {
        $this->status = $status;
        $this->handler = $handler;
        $this->arguments = $arguments ?: [];
    }

    /**
     * Get the route's request handler.
     *
     * @return RequestHandlerInterface
     */
    public function getHandler(): RequestHandlerInterface
    {
        return $this->handler;
    }

    /**
     * Get route arguments
     *
     * @param int $status
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Checks whether the route is found.
     *
     * @return bool
     */
    public function found(): bool
    {
        return $this->status === Dispatcher::FOUND;
    }

    /**
     * Checks whether the route is not allowed
     *
     * @return bool
     */
    public function notAllowed(): bool
    {
        return $this->status === Dispatcher::METHOD_NOT_ALLOWED;
    }
}
