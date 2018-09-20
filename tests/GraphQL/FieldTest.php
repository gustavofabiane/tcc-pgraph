<?php

declare(strict_types=1);

namespace Framework\Tests\GraphQL;

use Framework\GraphQL\Field;
use PHPUnit\Framework\TestCase;
use GraphQL\Type\Definition\Type;
use Framework\GraphQL\Definition\Field\PadField;
use Framework\GraphQL\Definition\Enum\PadDirection;

class FieldTest extends TestCase
{
    use GraphQLTestCaseTrait;

    public function setup()
    {
        $registry = $this->registry();
        $registry->addType(PadDirection::class);
    }

    public function fieldClassImplProvider()
    {
        $registry = $this->registry();
        $registry->addType(PadDirection::class);
        
        return [
            [
                new class($registry) extends Field {
                    public function name(): string
                    {
                        return 'padRight';
                    }
                    public function type(): Type
                    {
                        return $this->types->string();
                    }
                    public function args(): array
                    {
                        return [
                            'pad' => [
                                'type' => $this->types->string(),
                                'default_value' => '0'
                            ],
                            'size' => $this->types->int()
                        ];
                    }
                    public function resolve($src, array $args = [])
                    {
                        $value = $src->{$this->key};
                        return str_pad($value, $args['size'], $args['pad']);
                    }
                },
                'padRight',
                'fixedLenthString',
                ['pad' => '_', 'size' => 10],
                function () {
                    $obj = new \stdclass();
                    $obj->fixedLenthString = 'for-pad';
                    return $obj;
                },
                'for-pad___'
            ],
            [
                new PadField($registry),
                'pad',
                'fixedLength',
                ['pad' => '0', 'size' => 9],
                function () {
                    $obj = new \stdclass();
                    $obj->fixedLength = '123';
                    return $obj;
                },
                '123000000'
            ]
        ];
    }

    /**
     * @dataProvider fieldClassImplProvider
     */
    public function testFieldImplementation(
        $field,
        $defaultName,
        $srcKey,
        $arguments,
        $src,
        $resolved
    ) {
        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals($defaultName, $field->name());
        $this->assertSame($resolved, $field->make(null, $srcKey)->resolve($src(), $arguments));
    }

    public function testArrayAcessible()
    {
        $field = new PadField($this->registry());

        $this->assertArrayHasKey('name', $field);
        $this->assertEquals('pad', $field['name']);

        $field = $field->make('numberFixed', 'yearsOld');
        $this->assertEquals('numberFixed', $field['name']);
        $this->assertEquals('yearsOld', $field['key']);
        $this->assertEquals(
            'This field defines a string with a minimum ' .
            'length and complete its missing characters ' . 
            'with a PAD string defined by the client', 
            $field['description']
        );
        $this->assertTrue(isset($field['args']));

        $field['key'] = 'years';
        $this->assertEquals('years', $field->key());
    }

    public function testIteratorAggregateImplementation()
    {
        $field = new PadField($this->registry());
        $field = $field->make('numberFixed', 'yearsOld');

        $this->assertInstanceOf(\Traversable::class, $field);

        $stub = [
            'name' => 'numberFixed',
            'type' => $this->types->string(),
            'description' => 'This field defines a string with a minimum ' .
                             'length and complete its missing characters ' . 
                             'with a PAD string defined by the client',
            'args' => [
                'pad' => [
                    'type' => $this->types->string(),
                    'default_value' => '0'
                ], 
                'direction' => [
                    'type' => $this->types->padDirection(),
                    'default_value' => PadDirection::PAD_LEFT
                ],
                'size' => $this->types->int()
            ],
            'deprecationReason' => null,
            'complexity' => null
        ];

        foreach ($field as $property => $value) {
            $this->assertArrayHasKey($property, $stub);
            $this->assertEquals($stub[$property], $value);
        }
    }
}
