<?php

declare(strict_types=1);

namespace Framework\GraphQL;

abstract class Field
{
    /**
     * The field name
     *
     * @var string
     */
    protected $name;

    /**
     * Type registry instance
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
    }

    /**
     * Make the field definition array
     *
     * @param string $name
     * @return array
     */
    public final function make(string $name = null): array
    {
        return [
            'name' => $name ?: $this->name(),
            'type' => $this->type(),
            'args' => $this->arguments(),
            'resolve' => [$this, 'resolve']
        ];
    }

    /**
     * The name of the field
     * 
     * You can override this method to modify the default field name.
     *
     * @return string
     */
    public function name(): string 
    {
        return $this->name ?: $this->name = end(explode('\\', str_replace('Field', '', get_class($this))));
    }
    
    /**
     * The definition of the field's arguments
     *
     * @return array
     */
    public function arguments(): array
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
    abstract public function resolve($obj, array $args);
}