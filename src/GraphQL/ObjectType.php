<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use ArrayAccess;
use Framework\GraphQL\Util\TypeTrait;
use GraphQL\Type\Definition\ObjectType as BaseObjectType;

/**
 * Abstract implementation of custom enum type definitions
 */
abstract class ObjectType extends BaseObjectType
{
    use TypeTrait;
    
    /**
     * The type resgitry implementation instance.
     *
     * @var TypeRegistryInterface
     */
    protected $types;

    /**
     * Create a new object type instance.
     *
     * @param TypeRegistryInterface $types
     */
    public function __construct(TypeRegistryInterface $types)
    {
        $this->types = $types;
    }

    public final function make()
    {
        if (!$this->config) {
            parent::__construct([
                'description'  => $this->description(),
                'fields'       => $this->fields(), 
                'interfaces'   => $this->implements() ?: null,
                'resolveField' => $this->resoler()
            ]);
        }
    }

    /**
     * The object type description.
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description = sprintf(
            'An object type defined as \'%s\'', $this->name()
        );
    }

    /**
     * Return an iterable instance defining the type fields.
     *
     * @return iterable
     */
    abstract public function fields(): iterable;

    /**
     * Get the field resolver if its exists.
     *
     * @return callable|null
     */
    public final function resolver(): ?callable
    {
        if (method_exists($this, 'resolve')) {
            return [$this, 'resolve'];
        }
        return null;
    }

    protected function getFieldResolver($fieldName): callable
    {
        
    }

    /**
     * Interfaces that the object type implements.
     *
     * @return \GraphQL\Type\Definition\InterfaceType[]
     */
    public function implements(): array
    {
        return [];
    }

    public function isValidSource($value, $context, ResolveInfo $info): bool
    {
        return null;
    }
}