<?php

namespace Framework\Router;

use FastRoute\Dispatcher;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Http\Handlers\HasMiddlewareTrait;

class Route implements RouteInterface
{
    use HasMiddlewareTrait;

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
    public function __construct($handler, int $status = null)
    {
        $this->handler = $handler;
        $this->status = $status;
    }

    /**
     * Get the route's request handler.
     *
     * @return RequestHandlerInterface|callable
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set the route arguments
     *
     * @param array $arguments
     * @return void
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Set the route status
     *
     * @param int $status
     * @return void
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
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
