<?php

namespace Framework\Container;

use Framework\Container\ServiceResolverInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Implementation for the framework container that extends the PSR-11 interface
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Resolve and call a class, method or callable
     *
     * @param mixed $resolvable
     * @param array $parameters
     * @return mixed
     */
    public function resolve($resolvable, array $parameters = []);

    /**
     * Get the container service resolver
     *
     * @return ServiceResolverInterface
     */
    public function getResolver(): ServiceResolverInterface;
}