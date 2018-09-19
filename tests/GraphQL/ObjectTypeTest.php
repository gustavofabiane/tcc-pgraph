<?php

declare(strict_types=1);

namespace Framework\Tests\GraphQL;

use PHPUnit\Framework\TestCase;
use Framework\Tests\GraphQL\Stubs\StubObjectType;

class ObjectTypeTest extends TestCase
{
    use GraphQLTestCaseTrait;

    /**
     * Tested type
     *
     * @var \Framework\GraphQL\ObjectType
     */
    protected $objType;

    public function setup()
    {
        $this->objType = new StubObjectType($this->registry());
    }
}