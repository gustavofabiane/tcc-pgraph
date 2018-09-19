<?php

declare(strict_types=1);

namespace Framework\Tests\GraphQL;

use Framework\GraphQL\TypeRegistry;
use Framework\Container\Container;


/**
 * Helpers for GraphQL test cases
 */
trait GraphQLTestCaseTrait
{
    /**
     * Type registry
     *
     * @var TypeRegistry
     */
    protected $types;

    public function registry()
    {
        if (!$this->types) {
            $this->types = new TypeRegistry(new Container());
        }
        return $this->types;
    }
}
