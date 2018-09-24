<?php

declare(strict_types=1);

namespace Framework\GraphQL\Util;

use GraphQL\Error\Error;

/**
 * Undocumented class
 */
final class BasicErrorHandler
{
    /**
     * Format graphql schema errors.
     *
     * @param Error $error
     * @return array
     */
    public static function formatError(Error $error): array
    {
        return FormattedError::createFromException($error);
    }

    /**
     * Handle errors raised from GraphQL schema.
     *
     * @param array $errors
     * @param callable $formatter
     * @return array
     */
    public static function handleErrors(array $errors, callable $formatter): array
    {
        return array_map($formatter, $errors);
    }
}
