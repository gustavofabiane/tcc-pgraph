<?php

declare(strict_types=1);

namespace Framework\Tests\GraphQL;

use Framework\GraphQL\Fields;
use PHPUnit\Framework\TestCase;
use Framework\GraphQL\TypeRegistry;
use Framework\Tests\GraphQL\Stubs\StubObjectType;
use Framework\Tests\GraphQL\Stubs\StubInterfaceType;
use GraphQL\Type\Definition\FieldDefinition;

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
        $this->registry();
        $this->types->addType(StubInterfaceType::class);
        $this->objType = new StubObjectType();
        $this->objType->setTypeRegistry($this->types);
    }

    public function getFieldResolverTestProvider()
    {
        return [
            ['id', 'getIdField', md5('321')], 
            ['name', 'getNameField', 'static-name'], 
            ['floatNumber', 'getFloatNumberField', 321.99]
        ];
    }

    /**
     * @dataProvider getFieldResolverTestProvider
     *
     * @param string $field
     * @param string $resolverName
     * @return void
     */
    public function testGetResolver($field, $resolverName, $returnValue)
    {
        $reflectedMethod = new \ReflectionMethod($this->objType, 'getFieldResolver');
        $reflectedMethod->setAccessible(true);

        $resolver = $reflectedMethod->invoke($this->objType, $field);

        $this->assertNotNull($resolver);
        $this->assertEquals([$this->objType, $resolverName], $resolver);
        $this->assertTrue(is_callable($resolver));
        $this->assertEquals($returnValue, call_user_func($resolver, new \stdclass()));
    }

    public function testMakeFields()
    {
        $fields = Fields::create($this->objType);

        $this->assertEquals([
            [
                'name' => 'id',
                'type' => TypeRegistry::id(),
                'resolve' => [$this->objType, 'getIdField']
            ],
            // [
            //     'name' => 'name',
            //     'type' => TypeRegistry::string(),
            //     'resolve' => [$this->objType, 'getNameField']
            // ],
            [
                'name' => 'floatNumber',
                'type' => TypeRegistry::float(),
                'resolve' => [$this->objType, 'getFloatNumberField']
            ],
            FieldDefinition::create([
                'name' => 'name',
                'type' => TypeRegistry::string(),
                'resolve' => [$this->objType, 'getNameField']
            ])
        ], $fields());
    }
}