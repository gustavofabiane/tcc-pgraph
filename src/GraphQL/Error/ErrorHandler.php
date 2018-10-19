<?php

declare(strict_types=1);

namespace Framework\GraphQL\Error;

use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;

abstract class ErrorFormatter
{
    /**
     * Perform the default error handling.
     *
     * @final
     * @param Error[] $errors
     * @param callable|ErrorFormatter $formatter
     * @return array
     */
    public final function handleDefault(array $errors, callable $formatter): array
    {
        return array_map($formatter, $errors);
    }

    /**
     * Invoke the error handler as a callable.
     *
     * @final
     * @param Error[] $errors
     * @param callable|ErrorFormatter $formatter
     * @return array
     */
    public final function __invoke(array $errors, callable $formatter): array
    {
        return $this->handle($errors, $formatter) ?: $this->handleDefault($errors, $formatter);
    }

    /**
     * Children classes must implements this abstract method to 
     * handle errors raised in GraphQL schema execution. 
     * 
     * Note: this method can return a null value to indicate 
     * that the default formatting handler must be used.
     *
     * @param Error[] $errors
     * @param callable|ErrorFormatter $formatter
     * @return array|null
     */
    abstract public function handle(array $errors, callable $formatter): ?array;
}
