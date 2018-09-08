<?php

namespace Framework\Router;

use FastRoute\Dispatcher;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Http\Handlers\HasMiddlewareTrait;

class Route implements RouteInterface
{
    /**
     * Available rouet status
     */
    const ROUTE_STATUS = [
        Dispatcher::FOUND, 
        Dispatcher::NOT_FOUND, 
        Dispatcher::METHOD_NOT_ALLOWED
    ];

    /**
     * The route request handler.
     *
     * @var RouteRequestHandler
     */
    protected $handler;

    /**
     * The route path path
     *
     * @var string
     */
    protected $path;

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
     * @param string $path
     * @param int $status
     * @param RouteRequestHandler|null $handler
     * @param array|null $arguments
     */
    public function __construct(
        string $path,
        int $status,
        ?RouteRequestHandler $handler = null,
        ?array $arguments = []
    ) {
        $this->status = $this->filterStatus($status);
        $this->handler = $handler;
        $this->arguments = $arguments ?: [];

        if ($this->handler) {
            $this->handler->setRoute($this);
        }
    }

    /**
     * Filter route status
     *
     * @param int $status
     * @return int
     * 
     * @throws \InvalidArgumentException if the status is invalid
     */
    private function filterStatus(int $status): int
    {
        if (in_array($status, static::ROUTE_STATUS)) {
            return $status;
        }

        throw new \InvalidArgumentException(
            sprintf('Route status %u is not valid', $status)
        );
    }

    /**
     * Get the route's request handler.
     *
     * @return RequestHandlerInterface|null
     */
    public function getHandler(): ?RequestHandlerInterface
    {
        return $this->handler;
    }

    /**
     * Get the route path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
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
