<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\ListOfType;
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
     * Create a new type registry instance.
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

    /**
     * Check whether a given entry idntifier exists in the registry.
     *
     * @param string $entryTypeOrField
     * @return bool
     */
    public function exists(string $entryTypeOrField): bool
    {
        $entryTypeOrField = strtolower($entryTypeOrField);

        return isset($this->types[$entryTypeOrField]) || 
               isset($this->fields[$entryTypeOrField]);
    }

    /**
     * Assert if an entry exists in regitry.
     *
     * @param string $entryTypeOrField
     * @return void
     */
    protected function assertExistsInRegistry($entryTypeOrField)
    {
        if (!$this->exists($entryTypeOrField)) {
            throw new \RuntimeException(sprintf(
                'Entry [%s] does not exists in the type registry',
                $entryTypeOrField
            ));
        }
    }

    /**
     * Add a type to the register.
     *
     * @param Type|string $type
     * @param string $name
     * @return void
     */
    public function addType($type, string $name = null)
    {
        if (is_string($type) && !class_exists($type)) {
            $type = sprintf('%s\%s', $this->typesNamespace, ltrim($type, '\\'));
        }
        $typeKey = $name ?: $this->keyForType($type);
        
        $this->types[$typeKey] = $type;
    }
    
    /**
     * Add a field to the registry.
     *
     * @param Field|string $field
     * @param string $name
     * @return void
     */
    public function addField($field, string $name = null)
    {
        $fieldKey = $name ?: $this->keyForType($field);
        $this->fields[$fieldKey] = $field;
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
        $hold = $type;
        $type = strtolower($type);
        
        if (!isset($this->types[$type])) {
            throw new \InvalidArgumentException(
                sprintf('Given entry [%s] is not a valid registered type', $hold)
            );
        }

        if ($this->types[$type] instanceof Type) {
            return $this->types[$type];
        }

        $typeKey = $type;
        $type = $this->container->resolve(
            $this->types[$type], ['types' => $this]
        );
        if (method_exists($type, 'make')) {
            $type->make();
        }
        
        return $this->types[$typeKey] = $type;
    }
    
    /**
     * Get a field from the registry
     *
     * @param string $type
     * @param array $resolveWith
     * @return Type
     */
    public function field(string $field, string $name = null, string $key = null): Field
    {
        $this->assertExistsInRegistry($field);
        $hold = $field;
        $field = strtolower($field);
        
        if (!isset($this->fields[$field])) {
            throw new \InvalidArgumentException(
                sprintf('Given entry [%s] is not a valid registered field', $hold)
            );
        }

        if (! $this->fields[$field] instanceof Field) {
            $this->fields[$field] = $this->container->resolve(
                $this->fields[$field], 
                ['types' => $this]
            );
        }
        $field = $this->fields[$field];
        return $field->make($name, $key);
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
            return strtolower($type->name);
        }

        if ($type instanceof Field) {
            return strtolower($type->name());
        }

        if(class_exists($type)) {
            if (is_subclass_of($type, Type::class)) {
                $explodedTypeName = explode('\\', preg_replace('/(T|t)ype$/', '', $type));
                return strtolower(end($explodedTypeName));
            }
            if (is_subclass_of($type, Field::class)) {
                $explodedFieldName = explode('\\', preg_replace('/(F|f)ield$/', '', $type));
                return strtolower(end($explodedFieldName));
            }
        }

        throw new \InvalidArgumentException(
            sprintf('Cannot create key for given type or field \'%s\'', (string) $type)
        );
    }

    /**
     * Get a field or type definition from the registry
     *
     * @param string $typeOrField
     * @return Field|Type
     */
    public function get(string $typeOrField, string $name = null, string $key = null)
    {
        $this->assertExistsInRegistry($typeOrField);
        $typeOrField = strtolower($typeOrField);

        if (isset($this->types[$typeOrField])) {
            return $this->type($typeOrField);
        }
        return $this->field($typeOrField, $name, $key);
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

    /**
     * Wrap a type as Non Null
     *
     * @param Type|string $wrappedType
     * @return NonNull
     */
    public function nonNull($wrappedType): NonNull
    {
        return Type::nonNull(
            $wrappedType instanceOf Type 
            ? $wrappedType 
            : $this->type($wrappedType)
        );
    }

    /**
     * Wrap a type as a list
     *
     * @param Type|string $wrappedType
     * @return ListOfType
     */
    public function listOf($wrappedType): ListOfType
    {
        return Type::listOf(
            $wrappedType instanceOf Type 
            ? $wrappedType 
            : $this->type($wrappedType)
        );
    }
}
