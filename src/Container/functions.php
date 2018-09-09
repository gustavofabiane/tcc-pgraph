<?php

declare(strict_types=1);

namespace Framework\Container;

/**
 * Get an entry from the container.
 *
 * @param string $id
 * @return mixed
 */
function get(string $id)
{
    return Container::getInstance()->get($id);
}

/**
 * Check whether the entry exists in the container
 *
 * @param string $id
 * @return bool
 */
function has(string $id): bool
{
    return Container::getInstance()->has($id);
}

/**
 * Register an entry and its value in the container
 *
 * @param string $id
 * @param mixed $assembler
 * @param bool $singleton
 * @param array $value
 * @return void
 */
function register(string $id, $assembler, bool $singleton = false, array $parameters = [])
{
    Container::getInstance()->register($id, $assembler, $singleton, $parameters);
}

/**
 * Register an concrete implementatin that can be autowired by the container
 *
 * @param string $concrete
 * @param bool $singleton
 * @return void
 */
function autowire(string $concrete, bool $singleton = false)
{
    Container::getInstance()->register($concrete, null, $singleton);
}

/**
 * Bind an interface to its implementation
 *
 * @param string $interface
 * @param string $implementation
 * @return void
 */
function implemented(string $interface, string $implementation)
{
    Container::getInstance()->implemented($interface, $implementation);
}
