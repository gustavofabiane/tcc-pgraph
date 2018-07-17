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
        $this->container->add(ReflectionClass::class);
        $this->container->add('argument', Container::class);

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
        $this->container->add('tal', function (ContainerInterface $c) {
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
        $this->container->add('argument', $test);

        $assertTest = $this->container->get('argument');
        $this->assertEquals($test, $assertTest);

        $this->container->add(ReflectionClass::class);
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

        $this->container->add('array', $array);
        $this->container->add('int', $integer);
        $this->container->add('float', $float);
        $this->container->add('string', $string);

        $this->container->add('intfloat', function (int $integer, $float) {
            return $integer + $float;
        });
        $this->container->add('stringarray', function (string $string, array $array) {
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
        $this->container->add(StubClass::class);
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
