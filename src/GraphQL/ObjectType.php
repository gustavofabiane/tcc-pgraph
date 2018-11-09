<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use Framework\GraphQL\Fields;
use Framework\GraphQL\Util\TypeTrait;
use Framework\GraphQL\Util\TypeWithFields;
use Framework\GraphQL\Util\ImplementsInterface;
use GraphQL\Type\Definition\ObjectType as BaseObjectType;
use Framework\GraphQL\Util\MakeableType;

/**
 * Abstract implementation of an object type definitions.
 */
abstract class ObjectType extends BaseObjectType implements 
    TypeWithFields, 
    ImplementsInterface, 
    MakeableType
{
    use TypeTrait;

    /**
     * Make base type from implemented library.
     *
     * @return void
     */
    public final function make(): void
    {
        if (!$this->config) {
            parent::__construct([
                'description'  => $this->description(),
                'fields'       => Fields::create($this), 
                'interfaces'   => [$this, 'implements'],
                'resolveField' => $this->getTypeResolver(),
                // 'isTypeOf' ----> can override parent::isTypeOf
            ]);
            $this->setInstance($this);
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
     * Get the field resolver if its exists.
     *
     * @return callable|null
     */
    public final function getTypeResolver(): ?callable
    {
        if (method_exists($this, 'resolveField')) {
            return [$this, 'resolveField'];
        }
        return null;
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
}