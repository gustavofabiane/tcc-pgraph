<?php

namespace Framework\Tests\Container;

use ReflectionClass;
use PHPUnit\Framework\TestCase;
use Framework\Container\Container;
use Framework\Tests\Stubs\StubClass;
use Psr\Container\ContainerInterface;
use Framework\Tests\Stubs\StubInterface;
use Framework\Container\Exception\AliasTargetNotFoundException;

class ContainerTest extends TestCase
{
    /**
     * Tested Container
     *
     * @var Container
     */
    public $container;

    public function setup()
    {
        $this->container = new Container();
    }

    public function testAddClass()
    {
        $this->container->register(ReflectionClass::class);
        $this->container->register('argument', Container::class);

        $this->assertEquals(Container::class, $this->container->get('argument'));

        $reflectedContainer = $this->container->get(ReflectionClass::class);
        $this->assertInstanceOf(ReflectionClass::class, $reflectedContainer);
    }

    /**
     * Tests the service as callable in the container
     *
     * @return void
     */
    public function testAddCallable()
    {
        $this->container->register('tal', function (ContainerInterface $c) {
            return new ReflectionClass($c);
        });

        $this->assertEquals(true, $this->container->has('tal'));

        $tal = $this->container->get('tal');
        
        $this->assertInstanceOf(ReflectionClass::class, $tal);

        $this->assertEquals(Container::class, $tal->getName());
    }

    /**
     * Tests adding a class instance to the Container
     *
     * @return void
     */
    public function testAddInstance()
    {
        $test = new Container;
        $this->container->register('argument', $test);

        $assertTest = $this->container->get('argument');
        $this->assertEquals($test, $assertTest);

        $this->container->register(ReflectionClass::class);
        $reflectedContainer = $this->container->get(ReflectionClass::class);
        $this->assertInstanceOf(ReflectionClass::class, $reflectedContainer);
        $this->assertEquals(Container::class, $reflectedContainer->getName());
    }

    /**
     * Tests adding scalar and arrays values in the container
     *
     * @return void
     */
    public function testAddTypes()
    {
        $array = ['a', 1, 'b' => 'c'];
        $integer = 123;
        $float = 1.23;
        $string = 'abc';

        $this->container->register('array', $array);
        $this->container->register('int', $integer);
        $this->container->register('float', $float);
        $this->container->register('string', $string);

        $this->container->register('intfloat', function (int $integer, $float) {
            return $integer + $float;
        });
        $this->container->register('stringarray', function (string $string, array $array) {
            return $string . $array['b'];
        });

        $this->assertEquals($integer + $float, $this->container->get('intfloat'));
        $this->assertEquals($string . $array['b'], $this->container->get('stringarray'));
    }

    /**
     * Tests adding a implemented interface
     *
     * @return void
     */
    public function testAddImplementedInterface()
    {
        $this->container->implemented(StubInterface::class, StubClass::class);
        $implemented = $this->container->get(StubInterface::class);
        $this->isTrue(Container::implements($implemented, StubInterface::class));
        $this->assertInstanceOf(StubInterface::class, $implemented);
    }

    public function testAlias()
    {
        $this->container->register(StubClass::class);
        $this->container->alias('stub', StubClass::class);

        $stub = $this->container->get('stub');
        $this->assertInstanceOf(StubClass::class, $stub);
    }

    public function testAliasWithNoTarget()
    {
        $this->expectException(AliasTargetNotFoundException::class);
        
        $this->container->alias('stub', StubClass::class);
    }
}
