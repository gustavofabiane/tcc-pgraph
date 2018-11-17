<?php

declare(strict_types=1);

namespace Pgraph\GraphQL;

use Pgraph\GraphQL\Fields;
use Pgraph\GraphQL\Util\TypeTrait;
use Pgraph\GraphQL\Util\TypeWithFields;
use Pgraph\GraphQL\Util\ImplementsInterface;
use GraphQL\Type\Definition\InputObjectType as BaseInputObjectType;
use Pgraph\GraphQL\Util\MakeableType;

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
