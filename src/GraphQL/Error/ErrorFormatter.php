<?php

declare(strict_types=1);

namespace Framework\GraphQL\Error;

use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;

abstract class ErrorFormatter
{
    /**
     * Perform the default error formatting.
     *
     * @final
     * @param Error $error
     * @return array
     */
    public final function defaultFormat(Error $error): array
    {
        return FormattedError::createFromException($error);
    }

    /**
     * Invoke the error formatter as a callable.
     *
     * @final
     * @param Error $error
     * @return array
     */
    public final function __invoke(Error $error): array
    {
        return $this->formatError($error) ?: $this->defaultFormat($error);
    }

    /**
     * Children classes must implements this abstract method to 
     * format errors recieved from a GraphQL schema. 
     * 
     * Note: this method can return a null value to indicate 
     * that the default formatting handler must be used.
     *
     * @param Error $error
     * @return array|null
     */
    abstract public function formatError(Error $error): ?array;
}
