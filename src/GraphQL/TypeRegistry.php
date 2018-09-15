<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\BooleanType;
use Framework\Container\ContainerInterface;
use Framework\GraphQL\Exception\InvalidTypeException;

class TypeRegistry extends Type
{
    /**
     * Global type registry instance
     *
     * @var static
     */
    protected static $instance;

    /**
     * Registry container instance
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Types namespace used for class implemented types
     *
     * @var string
     */
    protected $typesNamespace;

    /**
     * Registered types
     *
     * @var array
     */
    protected $types = [];

    /**
     * Create a new type registry instance
     *
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container, 
        array $types = [], 
        $typesNamespace = ''
    ) {
        $this->container = $container;
        $this->typesNamespace = rtrim($typesNamespace, '\\');
        
        foreach($types as $type) {
            $this->set($type);
        }

        static::$instance = $this;
    }

    public function setType(Type $type)
    {
        
        return $this;
    }

    public function setField(Field $field)
    {

    }
    
    /**
     * Get a type from the registry
     *
     * @param string $type
     * @param array $resolveWith
     * @return Type
     */
    public function field(string $type): Type
    {
        $this->assertValidType($type);
        $type = $this->normalizeTypeName($type);
        
        return $this->types[$type] ?? $this->buildType($type);
    }

    /**
     * Get the ID internal type
     *
     * @return IDType
     */
    public function id(): IDType
    {
        return static::id();
    }

    /**
     * Get the Int internal type
     *
     * @return IntType
     */
    public function int(): IntType
    {
        return static::int();
    }

    /**
     * Get the String internal type
     *
     * @return StringType
     */
    public function string(): StringType
    {
        return static::string();
    }

    /**
     * Get the Float internal type
     *
     * @return FloatType
     */
    public function float(): FloatType
    {
        return static::float();
    }

    /**
     * Get the Boolean internal type
     *
     * @return BooleanType
     */
    public function boolean(): BooleanType
    {
        return static::boolean();
    }



    public function __call($type, $arguments): Type
    {
        return $this->get($type, $arguments);
    }

    public static function __callStatic($name, $arguments): Type
    {
        return static::getInstance()->get($type, $arguments);
    }
}
