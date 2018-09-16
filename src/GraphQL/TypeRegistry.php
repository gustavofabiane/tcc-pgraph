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

class TypeRegistry implements TypeRegistryInterface
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
     * Registered fields
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Create a new type registry instance
     *
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container, 
        $typesNamespace = ''
    ) {
        $this->container = $container;
        $this->typesNamespace = rtrim($typesNamespace, '\\');
        
        static::$instance = $this;
    }

    public function exists(string $entryTypeOrField): bool
    {
        return isset($this->types[$entryTypeOrField]) || 
               isset($this->types[$entryTypeOrField]);
    }

    protected function assertExistsInRegistry($entryTypeOrField)
    {
        if (!$this->exists($entryTypeOrField)) {
            throw new \RuntimeException(sprintf(
                'Entry [%s] does not exists in the type registry',
                $entryTypeOrField
            ));
        }
    }

    public function addType($type, string $name = null)
    {
        $typeKey = $name ?: $this->keyForType($type);
        $this->types[$typeKey] = $type;
    }
    
    public function addField($field, string $name = null)
    {
        $typeKey = $name ?: $this->keyForType($type);
        $this->fields[$typeKey] = $type;
    }

    /**
     * Get a type from the registry
     *
     * @param string $type
     * @return Type
     */
    public function type(string $type): Type
    {
        $this->assertExistsInRegistry($type);
        
        if ($this->types[$type] instanceof Type) {
            return $this->types[$type];
        }

        $type = $this->container->resolve($this->types[$type]);
        if (method_exists($type, 'make')) {
            $type->make();
        }

        return $this->types[$type] = $type;
    }
    
    /**
     * Get a field from the registry
     *
     * @param string $type
     * @param array $resolveWith
     * @return Type
     */
    public function field(string $field, string $name = null): Field
    {
        $this->assertExistsInRegistry($field);
        
        if (! $this->fields[$field] instanceof Field) {
            $this->fields[$field] = $this->container->resolve($this->fields[$field]);
        }
        $field = $this->fields[$field];
        return $field->make($name);
    }

    /**
     * Try to create a key value for a given type definition
     *
     * @param string|Type|Field $type
     * @return string
     */
    protected function keyForType($type): string
    {
        if ($type instanceof Type) {
            return $type->name;
        }

        if ($type instanceof Field) {
            return $type->name();
        }

        if(is_string($type)) {
            if (is_subclass_of($type, Type::class)) {
                return end(explode('\\', str_replace('Type', '', $type)));
            }
            if (is_subclass_of($type, Field::class)) {
                return end(explode('\\', str_replace('Field', '', $type)));
            }
        }

        throw new \InvalidArgumentException(
            sprintf('Cannot create key for given type \'%s\'', (string) $type)
        );
    }

    /**
     * Get a field or type definition from the registry
     *
     * @param string $typeOrField
     * @return Field|Type
     */
    public function get(string $typeOrField)
    {
        $this->assertExistsInRegistry($typeOrField);

        if (isset($this->types[$typeOrField])) {
            return $this->type($typeOrField);
        }
        return $this->field($typeOrField);
    }

    /**
     * Translates the type or field name call as a registry method to the get() method
     *
     * @param string $typeOrField
     * @param array $arguments
     * @return Field|Type
     */
    public function __call(string $typeOrField, array $arguments = null)
    {
        $typeOrField = ucfirst($typeOrField);
        return $this->get($typeOrField, ...$arguments);
    }

    /**
     * Translates the type or field name call method on static 
     * to the get() method of the global registry instance
     *
     * @param string $typeOrField
     * @param array $arguments
     * @return Field|Type
     */
    public static function __callStatic(string $typeOrField, array $arguments = null)
    {
        return static::$instance->{$typeOrField}(...$arguments);
    }

    /**
     * Get the ID internal type
     *
     * @return IDType
     */
    public function id(): IDType
    {
        return Type::id();
    }

    /**
     * Get the Int internal type
     *
     * @return IntType
     */
    public function int(): IntType
    {
        return Type::int();
    }

    /**
     * Get the String internal type
     *
     * @return StringType
     */
    public function string(): StringType
    {
        return Type::string();
    }

    /**
     * Get the Float internal type
     *
     * @return FloatType
     */
    public function float(): FloatType
    {
        return Type::float();
    }

    /**
     * Get the Boolean internal type
     *
     * @return BooleanType
     */
    public function boolean(): BooleanType
    {
        return Type::boolean();
    }
}
