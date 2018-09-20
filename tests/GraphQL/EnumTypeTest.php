<?php

declare(strict_types=1);

namespace Framework\Tests\GraphQL;

use PHPUnit\Framework\TestCase;
use Framework\Tests\GraphQL\Stubs\StubEnumType;

class EnumTypeTest extends TestCase
{
    use GraphQLTestCaseTrait;

    /**
     * Test Enum instance
     *
     * @var StubEnumType
     */
    protected $enum;

    public function setup()
    {
        $this->enum = new StubEnumType();
        $this->enum->setTypeRegistry($this->registry());
        $this->enum->make();
    }

    public function testGetValues()
    {
        $this->assertTrue(method_exists($this->enum, 'values'));
        $this->assertEquals(['ONE', 'TWO', 'THREE'], $this->enum->values());
    }

    public function testInferName()
    {
        $this->assertEquals('StubEnum', $this->enum->name);
    }

    public function testGetDescription()
    {
        $this->assertEquals('There is a stub description.', $this->enum->description());
    }

    public function enumValueHelperProvider()
    {
        return [
            [
                ['VALUE_NAME', 10, 'This is a enum value', 'And it\'s deprecated'],
                [
                    'name' => 'VALUE_NAME',
                    'value' => 10,
                    'description' => 'This is a enum value',
                    'deprecationReason' => 'And it\'s deprecated'
                ]
            ], [
                ['value two'],
                [
                    'name' => 'VALUE_TWO',
                    'value' => 'VALUE_TWO'
                ]
            ], [
                ['VALUE_THREE', 'test', 'Enum value with description'],
                [
                    'name' => 'VALUE_THREE',
                    'value' => 'test',
                    'description' => 'Enum value with description'
                ]
            ]    
        ];
    }

    /**
     * @dataProvider enumValueHelperProvider
     *
     * @return void
     */
    public function testEnumValueHelper($parameters, $expected)
    {
        $value = call_user_func_array('Framework\GraphQL\enumValue', $parameters);
        $this->assertEquals($expected, $value);
    }
}
