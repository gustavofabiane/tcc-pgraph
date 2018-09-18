<?php

declare(strict_types=1);

namespace Framework\Tests\GraphQL;

use Framework\GraphQL\Field;
use PHPUnit\Framework\TestCase;
use GraphQL\Type\Definition\Type;
use Framework\Container\Container;
use Framework\GraphQL\TypeRegistry;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\BooleanType;
use Framework\GraphQL\TypeRegistryInterface;

class TypeRegistryTest extends TestCase
{
    /**
     * Type registry
     *
     * @var TypeRegistry
     */
    protected $types;

    /**
     * 
     * 
     * @return void
     */
    public function setup()
    {
        $this->types = new TypeRegistry(
            new Container(),
            'Framework\Tests\GraphQL\Type'
        );
        $this->types->addType($this->stubType('StubForTest'));
        $this->types->addField($this->stubField('stubFieldForTest'));
    }

    protected function stubType($name = null)
    {
        return new ObjectType([
            'name' => $name ?: 'StubType',
            'fields' => [
                'id' => $this->types->id(),
                'name' => $this->types->string()
            ],
            'resolve' => function ($src) {
                return [
                    'id' => '12345',
                    'name' => 'Stub Name'
                ];
            }
        ]);
    }

    protected function stubField($name = null)
    {
        $field = new class($this->types) extends Field {
            public function type(): Type
            {
                return $this->types->int();
            }
            public function resolve($src, array $args = [])
            {
                return $src->stubField * 2;
            }
        };

        return $field->make($name);
    }

    public function testCreateInstance()
    {
        $this->assertInstanceOf(TypeRegistryInterface::class, $this->types);
    }

    public function testGetInternalTypes()
    {
        $this->assertInstanceOf(IDType::class, $this->types->id());
        $this->assertInstanceOf(IntType::class, $this->types->int());
        $this->assertInstanceOf(FloatType::class, $this->types->float());
        $this->assertInstanceOf(StringType::class, $this->types->string());
        $this->assertInstanceOf(BooleanType::class, $this->types->boolean());
    }

    public function testAddType()
    {
        $this->types->addType($this->stubType('StubType'));

        $this->assertTrue($this->types->exists('StubType'));
        $this->assertInstanceOf(ObjectType::class, $this->types->stubType());
    }

    public function testAddField()
    {
        $this->types->addField($this->stubField('stubField'));

        $this->assertTrue($this->types->exists('stubField'));
        $this->assertInstanceOf(Field::class, $this->types->stubField());
    }

    public function testCallStatic()
    {   
        $typeFromStatic = TypeRegistry::stubForTest();
        $this->assertInstanceOf(Type::class, $typeFromStatic);
        $this->assertEquals('StubForTest', $typeFromStatic->name);
    }

    public function testGetNonExistentType()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Entry [notRegistered] does not exists in the type registry');

        $this->types->notRegistered();
    }
    
    public function testGetTypeAsField()
    {   
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Given entry [StubForTest] is not a valid registered field');

        $this->types->field('StubForTest');
    }
    
    public function testGetFieldAsType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Given entry [stubFieldForTest] is not a valid registered type');

        $this->types->type('stubFieldForTest');
    }
}