<?php

declare(strict_types=1);

namespace Pgraph\GraphQL;

use GraphQL\Language\AST\Node;
use Pgraph\GraphQL\Util\TypeTrait;
use GraphQL\Type\Definition\ScalarType as BaseScalarType;
use Pgraph\GraphQL\Util\MakeableType;

/**
 * Abstract implementation of a scalar type definitions.
 */
abstract class ScalarType extends BaseScalarType implements 
    MakeableType
{
    use TypeTrait;

    /**
     * Create a new ScalarType instance.
     */
    public function __construct()
    {
        ///
    }


    public function make(): void
    {
        if (!$this->name) {
            $this->name = $this->tryInferName();
        }
    }
    
    /**
     * Serializes an internal value to include in a response.
     *
     * @param mixed $value
     * @return string
     */
    abstract public function serialize($value);

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
     * @param array $variables
     * @return string
     * @throws \GraphQL\Error\Error
     */
    abstract public function parseLiteral($valueNode, array $variables = null);
}