<?php

namespace Container;

use Psr\Container\ContainerInterface;

/**
 * Interface used to implement resolvers
 */
interface ServiceResolverInterface
{
    /**
     * Resolves a class, method or Closure
     *
     * @param mixed $resolvable
     * @param ContainerInterface $container
     * @return mixed
     */
    public function resolve($resolvable, ContainerInterface $container);
}
