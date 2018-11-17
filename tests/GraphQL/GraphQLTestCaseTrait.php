<?php

declare(strict_types=1);

namespace Pgraph\Tests\GraphQL;

use Pgraph\GraphQL\TypeRegistry;
use Pgraph\Container\Container;


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
