<?php

namespace Framework\Container;

use Psr\Container\ContainerInterface;

/**
 * Interface used to implement resolvers
 */
interface ServiceResolverInterface
{
    /**
     * RegEx pattern for string resolvables
     */
    const RESOLVABLE_PATTERN = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';
    
    /**
     * Resolves a class, method or Closure
     *
     * @param mixed $resolvable
     * @param bool $deffered define that the resolver instance will return a Closure for deffered resolving
     * @return callable
     */
    public function resolve($resolvable, bool $deffered = false, array $parameters = []);

    public function setContainer(ContainerInterface $container);

    public function getContainer(): ContainerInterface;
}
