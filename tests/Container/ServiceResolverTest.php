<?php

namespace Pgraph\Tests\Container;

use PHPUnit\Framework\TestCase;
use Pgraph\Container\Container;
use Pgraph\Tests\Stubs\StubClass;
use Pgraph\Container\ServiceResolver;
use Pgraph\Container\Exception\ContainerException;

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
        $this->container->register('int', 9);
        $this->container->register('array', [2, 1, 2, 0]);

        $closure = function (int $numberOne, array $arrayTest) {
            return $numberOne / $arrayTest[0];
        };
        
        $result = $this->resolver->resolve($closure, ['arrayTest' => [3, 2, 1]]);
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
        $this->container->register('int', 1);

        $result = $this->resolver->resolve(
            'Pgraph\Tests\Stubs\StubClass:toResolve', 
            ['userDefinedParam' => 5]
        );

        $this->assertEquals(6, $result);
    }

    public function testResolveClassMethodArray()
    {
        $this->container->register('int', 2);

        $result = $this->resolver->resolve(
            ['Pgraph\Tests\Stubs\StubClass', 'toResolve'], 
            ['userDefinedParam' => 5]
        );

        $this->assertEquals(7, $result);
    }

    public function testResolveObjectMethodCallable()
    {
        $this->container->register('int', 2);

        $stub = new StubClass();
        $result = $this->resolver->resolve([$stub, 'toResolve'], ['userDefinedParam' => 5.5]);

        $this->assertEquals(7.5, $result);
    }

    public function testResolveWithMethodDefaultParameters()
    {
        $result = $this->resolver->resolve('Pgraph\Tests\Stubs\StubClass:toResolveDefault');
        $this->assertEquals(800, $result);
    }

    public function testNoDefaultValueAvailableForResolver()
    {
        $this->expectException(ContainerException::class);
        $result = $this->resolver->resolve('Pgraph\Tests\Stubs\StubClass:toResolve');
    }
}