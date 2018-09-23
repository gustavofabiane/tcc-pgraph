<?php

declare(strict_types=1);

namespace Framework\Tests\GraphQL;

use PHPUnit\Framework\TestCase;
use Framework\GraphQL\UnionType;
use Framework\Tests\GraphQL\Stubs\StubUnionType;
use Framework\Tests\GraphQL\Stubs\StubObjectType;
use Framework\Tests\GraphQL\Stubs\StubObjectTwoType;
use GraphQL\Type\Definition\ResolveInfo;

class UnionTypeTest extends TestCase
{
    use GraphQLTestCaseTrait;

    /**
     * Test Union type
     *
     * @var UnionType
     */
    protected $union;

    public function setup()
    {
        $this->union = new StubUnionType($this->registry());
        $this->union->setTypeRegistry($this->types);

        $this->types->addType(StubObjectType::class);
        $this->types->addType(StubObjectTwoType::class);

        $this->union->make();
    }

    public function testGetUnionTypes()
    {
        $types = [
            $this->types->type('StubObject'),
            $this->types->type('StubObjectTwo'),
        ];
        $this->assertEquals($types, $this->union->getTypes());
    }

    public function testResolveType()
    {
        $value = new \stdclass();
        $value->type = 'one';

        $type = $this->union->resolveType($value, null, new ResolveInfo([]));

        $this->assertInstanceOf(StubObjectType::class, $type);
        $this->assertEquals('StubObject', $type->name);
    }
}
