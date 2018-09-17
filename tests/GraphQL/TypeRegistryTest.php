<?php

declare(strict_types=1);

namespace Framework\Tests\GraphQL;

use PHPUnit\Framework\TestCase;
use Framework\Container\Container;
use Framework\GraphQL\TypeRegistry;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\BooleanType;
use Framework\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition\ObjectType;

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
        $this->types->addType(new ObjectType([
            'name' => 'StubType',
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
        ]));

        $this->assertTrue($this->types->exists('StubType'));
        $this->assertInstanceOf(ObjectType::class, $this->types->stubType());

        // $type = $this->types->type('StubType');
        // $type->
    }
}