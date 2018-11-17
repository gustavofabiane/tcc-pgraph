<?php

namespace Pgraph;

/**
 * Check whether the concrete type implements the given interface.
 *
 * @param string|object $concrete
 * @param string $interface
 * @return bool
 */
function isImplementerOf($concrete, string $interface): bool
{
    if (
        $concrete instanceof \Closure ||
        (!is_string($concrete) && !is_object($concrete)) || 
        (is_string($concrete) && function_exists($concrete))
    ) {
        return false;
    }
    $implements = class_implements($concrete);
    return array_key_exists($interface, $implements);
}
