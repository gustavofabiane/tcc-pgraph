<?php

namespace Framework\Tests\Container;

use Framework\Container;
use PHPUnit\Framework\TestCase;
use Framework\Tests\Stubs\StubClass;
use Framework\Tests\Stubs\Stub2Class;
use Framework\Tests\Stubs\StubInterface;
use Framework\Container\Container as Impl;

class ContainerFunctionsTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        Impl::setInstance(new Impl());
    }

    public static function tearDownAfterClass()
    {
        Impl::setInstance(new Impl());
    }

    public function testRegister()
    {
        Container\register('stub', 'Framework\Tests\Stubs\StubClass');
        $this->assertTrue(Impl::getInstance()->has('stub'));
    }

    public function testHas()
    {
        $this->assertFalse(Container\has('some-entry'));

        Container\register('stub', 'Framework\Tests\Stubs\Stub2Class');
        $this->assertTrue(Container\has('stub'));
    }

    public function testGet()
    {
        Container\register('stub', function () {
            return new StubClass();
        });
        $this->assertInstanceOf(StubClass::class, Container\get('stub'));
    }

    public function testAutowire()
    {
        Container\autowire(Stub2Class::class);
        $this->assertInstanceOf(Stub2Class::class, Container\get(Stub2Class::class));
    }

    public function testImplemented()
    {
        Container\register(StubClass::class, function () {
            return new StubClass('barbazfoo', '', true); 
        });
        Container\implemented(StubInterface::class, StubClass::class);
        $resolved = Container\get(StubClass::class);

        $this->assertInstanceOf(StubInterface::class, $resolved);
        $this->assertInstanceOf(StubClass::class, $resolved);
        $this->assertEquals('barbazfoo', $resolved->getFooBar());
    }
}
