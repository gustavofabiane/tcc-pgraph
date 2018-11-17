<?php

declare(strict_types=1);

namespace Pgraph\GraphQL;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType as BaseScalarType;

/**
 * Abstract implementation of a scalar type definitions.
 */
abstract class ScalarType extends BaseScalarType
{
    
    /**
     * Serializes an internal value to include in a response.
     *
     * @param string $value
     * @return string
     */
    abstract public function serialize(string $value): string;

    /**
     * Parses an externally provided value (query variable) to use as an input.
     *
     * @param mixed $value
     * @return mixed
     */
    abstract public function parseValue($value);

    /**
     * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input.
     * 
     * E.g. 
     * {
     *   user(email: "user@example.com") 
     * }
     *
     * @param Node $valueNode
     * @return string
     * @throws \GraphQL\Error\Error
     */
    abstract public function parseLiteral(Node $valueNode): string;
}