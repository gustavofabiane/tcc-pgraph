<?php

namespace Framework;

/**
 * Check whether the concrete type implements the given interface.
 *
 * @param string|object $concrete
 * @param string $interface
 * @return bool
 */
function isImplementerOf($concrete, string $interface): bool
{
    if ((!is_string($concrete) && !is_object($concrete)) || function_exists($concrete)) {
        return false;
    }
    $implements = class_implements($concrete);
    return array_key_exists($interface, $implements);
}
