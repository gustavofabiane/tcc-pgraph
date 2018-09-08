<?php

namespace Framework\Container;

use Psr\Container\ContainerInterface;

/**
 * Interface used to implement resolvers
 */
interface ServiceResolverInterface
{
    /**
     * RegEx pattern for string class:method resolvables
     */
    const RESOLVABLE_PATTERN = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';
    
    /**
     * Resolves a class, method or Closure|callable
     *
     * MUST accept the following structures:
     * -> a class name
     * -> a string with a class name and a method name separated by ':', ex: class:method
     * -> a \Closure instance
     * -> a callable
     *
     * @param mixed $resolvable
     * @return mixed
     */
    public function resolve($resolvable, array $parameters = []);

    /**
     * Defines a ContainerInterface instance to be used by the resolver
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Retrieves the ContainerInterface instance used by the resolver
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;
}
