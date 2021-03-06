<?php

declare(strict_types=1);

namespace Pgraph\GraphQL;

use Pgraph\GraphQL\Util\TypeTrait;
use GraphQL\Type\Definition\EnumType as BaseEnumType;
use Pgraph\GraphQL\Util\MakeableType;

/**
 * Abstract implementation of custom enum type definitions
 */
abstract class EnumType extends BaseEnumType implements MakeableType
{
    use TypeTrait;

    /**
     * The values accepted by the enum type
     *
     * @var array
     */
    public $values;

    /**
     * Make the enum type definition
     *
     * @param string $name
     * @return void
     */
    public final function make(): void
    {
        if (!$this->config) {
            parent::__construct([
                'description' => $this->description ?: $this->description(),
                'values'      => $this->values ?: $this->values()
            ]);
            $this->setInstance($this);
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
            'An enum type defined as \'%s\'', $this->name
        );
    }

    /**
     * The enum type accepted values
     *
     * @return array
     */
    abstract public function values(): array;
}