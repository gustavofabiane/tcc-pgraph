<?php

namespace Pgraph\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Implementation for the framework container that extends the PSR-11 interface
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Resolve and call a class, method or callable
     *
     * MUST accept the follow patterns:
     *
     *  - 'functionName'                        | string
     *  - 'Namespace\ClassName'                 | string
     *  - 'Namespace\ClassName:methodName'      | string
     *  - 'ClassName::staticMethod              | string
     *  - ['Namespace\ClassName', 'methodName'] | array
     *  - [$object, 'methodName']               | array
     *  - $function                             | \Closure
     *
     * @param mixed $resolvable
     * @param array $parameters
     * @return mixed
     */
    public function resolve($resolvable, array $parameters = []);
}
