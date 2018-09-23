<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use Framework\GraphQL\Fields;
use Framework\GraphQL\Util\TypeTrait;
use Framework\GraphQL\Util\TypeWithFields;
use GraphQL\Type\Definition\InterfaceType as BaseInterfaceType;

/**
 * Abstract implementation of an interface type definitions.
 */
abstract class InterfaceType extends BaseInterfaceType implements TypeWithFields
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
                'fields'      => Fields::create($this), 
                'description' => $this->description(),
                'resolveType' => [$this, 'resolveType'] 
                // ---> can override parent::resolveType()
            ]);
            $this->setInstance($this);
        }
    }

    /**
     * The interface type description.
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description = sprintf(
            'An interface type defined as \'%s\'', $this->name()
        );
    }
}