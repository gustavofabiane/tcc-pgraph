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
     * The enum type name
     *
     * @var string
     */
    protected $name;

    /**
     * The enum type description
     *
     * @var string
     */
    protected $description;

    /**
     * The values accepted by the enum type
     *
     * @var array
     */
    protected $values;

    /**
     * The enum type instance
     *
     * @var static
     */
    protected static $instance;

    /**
     * Make the enum type definition
     *
     * @param string $name
     * @return EnumType
     */
    public final function make(): EnumType
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