<?php

namespace Framework\GraphQL;

use Psr\Container\ContainerInterface;
use GraphQL\Type\Definition\Type;

class TypeRegistry
{
    /**
     * Registry container instance
     *
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __call($type, $arguments): Type
    {
        return $this->get($type, $arguments);
    }

    public static function __callStatic($name, $arguments): Type
    {
        return static::getInstance()->get($type, $arguments);
    }

    public function get(string $type, array $arguments): Type
    {

    }
}
