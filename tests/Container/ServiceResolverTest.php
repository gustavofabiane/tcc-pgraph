<?php

namespace Framework\Tests\Container;

use PHPUnit\Framework\TestCase;
use Framework\Container\Container;
use Framework\Tests\Stubs\StubClass;
use Framework\Container\ServiceResolver;
use Framework\Container\Exception\EntryNotFoundException;

class ServiceResolverTest extends TestCase
{
    /**
     * Container instance
     *
     * @var Container
     */
    private $container;

    /**
     * The resolver instance
     *
     * @var ServiceResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->container = new Container();
        $this->resolver = $this->container->getResolver();
    }

    /**
     * Tests if the resolver can work properly both without default parameters or with it
     *
     * @return void
     */
    public function testResolveClosureWithDefaultParameter()
    {
        $this->container->add('int', 9);
        $this->container->add('array', [2, 1, 2, 0]);

        $closure = function (int $numberOne, array $arrayTest) {
            return $numberOne / $arrayTest[0];
        };
        
        $result = $this->resolver->resolve($closure, false, ['arrayTest' => [3, 2, 1]]);
        $this->assertEquals(3, $result);

        $resultOnlyContainer = $this->resolver->resolve($closure);
        $this->assertEquals(4.5, round($resultOnlyContainer, 1));
    }

    /**
     * Tests resolver for class:method pattern
     *
     * @return void
     */
    public function testResolveClassMethodPattern()
    {
        $this->container->add('int', 1);

        $result = $this->resolver->resolve(
            'Framework\Tests\Stubs\StubClass:toResolve', 
            false, 
            ['userDefinedParam' => 5]
        );

        $this->assertEquals(6, $result);
    }

    public function testResolveClassMethodArray()
    {
        $this->container->add('int', 2);

        $result = $this->resolver->resolve(
            ['Framework\Tests\Stubs\StubClass', 'toResolve'], 
            false, 
            ['userDefinedParam' => 5]
        );

        $this->assertEquals(7, $result);
    }

    public function testResolveObjectMethodCallable()
    {
        $this->container->add('int', 2);

        $stub = new StubClass();
        $result = $this->resolver->resolve([$stub, 'toResolve'], false, ['userDefinedParam' => 5.5]);

        $this->assertEquals(7.5, $result);
    }

    public function testResolveWithMethodDefaultParameters()
    {
        $result = $this->resolver->resolve('Framework\Tests\Stubs\StubClass:toResolveDefault');
        $this->assertEquals(800, $result);
    }

    public function testNoDefaultValueAvailableForResolver()
    {
        $this->expectException(EntryNotFoundException::class);

        $result = $this->resolver->resolve('Framework\Tests\Stubs\StubClass:toResolve');
    }

    public function testResolveDeffered()
    {
        $result = $this->resolver->resolve('Framework\Tests\Stubs\StubClass:toResolveDefault', true);
        $this->assertInstanceOf(\Closure::class, $result);

        $concrete = $result();
        $this->assertInternalType('int', $concrete);
        $this->assertEquals(800, $concrete);
    }
}