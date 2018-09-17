<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use ArrayAccess;
use GraphQL\Type\Definition\Type;

/**
 * Abstract implementation of custom field definitions.
 */
abstract class Field implements ArrayAccess
{
    /**
     * The source object field key name.
     *
     * @var string
     */
    protected $key = 'dummy';

    /**
     * The field name.
     *
     * @var string
     */
    protected $name;

    /**
     * The field description.
     *
     * @var string
     */
    protected $description;

    /**
     * The field type.
     *
     * @var Type
     */
    protected $type;

    /**
     * The field arguments.
     *
     * @var array
     */
    protected $args = [];

    /**
     * The field resolver method as callable.
     *
     * @var callable
     */
    protected $resolve;

    /**
     * Type registry instance.
     *
     * @var TypeRegistry
     */
    protected $types;

    /**
     * Create a new field instance
     *
     * @param TypeRegistryInterface $types
     */
    public function __construct(TypeRegistryInterface $types)
    {
        $this->types = $types;
        
        $this->name = $this->name ?: $this->name();
        $this->key = $this->key ?: $this->key();
        $this->args = $this->args ?: $this->args();
        $this->description = $this->description ?: $this->description();
        $this->type = $this->type ?: $this->type();
        $this->resolve = [$this, 'resolve'];
    }

    /**
     * Make the field definition array
     *
     * @param string $name
     * @return array
     */
    public final function make(?string $name = null, ?string $key = null)
    {
        $clone = clone $this;
        
        if ($name) {
            $clone->name = $name;
        }
        if ($key) {
            $clone->key = $key;
        }
        $clone->resolve = [$clone, 'resolve'];

        return $clone;
    }

    /**
     * The name of the field
     * 
     * Note: You can override this method to modify the default field name.
     *
     * @return string
     */
    public function name(): string 
    {
        $explodedFieldName = explode('\\', str_replace('Field', '', get_class($this)));
        return $this->name = lcfirst(end($explodedFieldName));
    }

    /**
     * The source object key field key name.
     * 
     * Note: You can override this method to modify the default key name.
     *
     * @return string
     */
    public function key(): string
    {
        return $this->key ?: ($this->name ?: $this->name());
    }

    /**
     * The description of the field.
     * 
     * Note: You can override this method to modify the default description.
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description = sprintf(
            'Custom field defined as \'%s\'', $this->name
        );
    }
    
    /**
     * The definition of the field's arguments
     *
     * Note: You MUST override this method to modify the field arguments.
     * 
     * @return array
     */
    public function args(): array
    {
        return [];
    }

    /**
     * When implemented, MUST return the type which the field is defined
     *
     * @return Type
     */
    abstract public function type(): Type;

    /**
     * Handle the field resolution
     *
     * @param object|array $obj
     * @param array $args
     * @return mixed
     */
    abstract public function resolve($obj, array $args = []);

    /**
     * ArrayAccess implementation
     */
    public function offsetGet($offset)
    {
        return $this->{$offset} ?? null;
    }

    /**
     * ArrayAccess implementation
     */
    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            $this->{$offset} = $value;
        }
    }

    /**
     * ArrayAccess implementation
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
     * ArrayAccess implementation
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->{$offset} = null;
        }
    }
}