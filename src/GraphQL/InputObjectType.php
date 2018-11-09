<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use Framework\GraphQL\Fields;
use Framework\GraphQL\Util\TypeTrait;
use Framework\GraphQL\Util\TypeWithFields;
use Framework\GraphQL\Util\ImplementsInterface;
use GraphQL\Type\Definition\InputObjectType as BaseInputObjectType;
use Framework\GraphQL\Util\MakeableType;

/**
 * Abstract implementation of an object type definitions.
 */
abstract class InputObjectType extends BaseInputObjectType implements 
    TypeWithFields, 
    MakeableType
{
    use TypeTrait;

    /**
     * Make base type from implemented library.
     *
     * @return void
     */
    public final function make()
    {
        if (!$this->config) {
            parent::__construct([
                'description' => $this->description(),
                'fields'      => InputFields::create($this), 
            ]);
            $this->setInstance($this);
        }
    }

    /**
     * The input object type description.
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description = sprintf(
            'An input object type defined as \'%s\'', $this->name()
        );
    }
}
