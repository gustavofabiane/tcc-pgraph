<?php

namespace Framework\Tests\GraphQL;

use Framework\GraphQL\Field;
use PHPUnit\Framework\TestCase;
use GraphQL\Type\Definition\Type;
use Framework\Container\Container;
use Framework\GraphQL\TypeRegistry;
use Framework\GraphQL\Definition\Field\PadField;

class FieldTest extends TestCase
{
    public function fieldClassImplProvider()
    {
        $registry = new TypeRegistry(new Container());
        return [
            [
                new class($registry) extends Field {
                    public function name(): string {
                        return 'padRight';
                    }
                    public function type(): Type {
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
                    public function resolve($src, $args) {
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
    public function testFieldImplementation($field, $defaultName, $srcKey, $arguments, $src, $resolved)
    {
        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals($defaultName, $field->name());
        $this->assertSame($resolved, $field->make(null, $srcKey)->resolve($src(), $arguments));
    }
}
