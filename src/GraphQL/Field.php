<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use GraphQL\Type\Definition\Type;
use Framework\GraphQL\Util\ArrayAccessTrait;

/**
 * Abstract implementation of custom field definitions.
 */
abstract class Field implements ArrayAccess, IteratorAggregate
{
    use ArrayAccessTrait;

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
     * Used to restrict query complexity. 
     * Implement a method called 'complexity' with the proper signature to use this property.
     * 
     * Note: The feature is disabled by default, read about Security to use it.
     * 
     * @see http://webonyx.github.io/graphql-php/security/#query-complexity-analysis
     * 
     * @var callable
     */
    protected $complexity;

    /**
     * The field deprecated reason.
     *
     * @var string
     */
    protected $deprecationReason;

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
        
        $this->key = $this->key ?: $this->key();
        $this->name = $this->name ?: $this->name();
        $this->args = $this->args ?: $this->args();
        $this->type = $this->type ?: $this->type();
        
        $this->description = $this->description ?: $this->description();
        $this->deprecationReason = $this->deprecationReason ?: $this->deprecationReason();

        $this->resolve = [$this, 'resolve'];
        $this->complexity = method_exists($this, 'complexity') ? [$this, 'complexity'] : null;
    }

    /**
     * Make the field definition array
     *
     * @param string $name
     * @return array
     */
    public final function make(?string $name = null, ?string $key = null, ?string $deprecationReason = null)
    {
        $clone = clone $this;
        
        if ($name) {
            $clone->name = $name;
        }
        if ($key) {
            $clone->key = $key;
        }
        if ($deprecationReason) {
            $clone->deprecationReason = $deprecationReason;
        }
        if ($this->complexity) {
            $clone->complexity = [$clone, 'complexity'];
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
        if (!$this->name) {
            $explodedFieldName = explode('\\', str_replace('Field', '', get_class($this)));
            $this->name = lcfirst(end($explodedFieldName));
        }
        return $this->name;
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
     * The field deprecation reason.
     * 
     * Note: Override this method to mark the field as deprecated in all schema.
     *
     * @return string
     */
    public function deprecationReason(): ?string
    {
        return null;
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
     * Implements IteratorAggregate interface
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator([
            'name' => $this->name,
            'type' => $this->type,
            'args' => $this->args,
            'description' => $this->description,
            'deprecationReason' => $this->deprecationReason,
            'complexity' => $this->complexity
        ]);
    }
}