<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use ArrayAccess;
use GraphQL\Type\Definition\EnumType;

/**
 * Abstract implementation of custom enum type definitions
 */
abstract class Enum extends EnumType
{
    /**
     * The type resgitry implementation instance
     *
     * @var TypeRegistryInterface
     */
    protected $types;

    /**
     * The enum type name
     *
     * @var string
     */
    public $name;

    /**
     * The enum type description
     *
     * @var string
     */
    public $description;

    /**
     * The values accepted by the enum type
     *
     * @var array
     */
    public $values;

    /**
     * The enum type instance
     *
     * @var static
     */
    protected static $instance;

    /**
     * Create a new enum type instance
     *
     * @param TypeRegistryInterface $types
     */
    public function __construct(TypeRegistryInterface $types)
    {
        $this->types = $types;
    }

    /**
     * Make the enum type definition
     *
     * @param string $name
     * @return void
     */
    public final function make()
    {
        if ($this->config) {
            parent::__construct([
                'description' => $this->description ?: $this->description(),
                'values'      => $this->values ?: $this->values()
            ]);
        }
    }

    /**
     * The type description
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description = sprintf(
            'An enum type defined as \'%s\'', $this->name()
        );
    }

    /**
     * The enum type accepted values
     *
     * @return array
     */
    abstract public function values(): array;
}