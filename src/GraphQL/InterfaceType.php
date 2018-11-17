<?php

declare(strict_types=1);

namespace Pgraph\GraphQL;

use Pgraph\GraphQL\Fields;
use Pgraph\GraphQL\Util\TypeTrait;
use Pgraph\GraphQL\Util\TypeWithFields;
use GraphQL\Type\Definition\InterfaceType as BaseInterfaceType;
use Pgraph\GraphQL\Util\MakeableType;

/**
 * Abstract implementation of an interface type definitions.
 */
abstract class InterfaceType extends BaseInterfaceType implements 
    TypeWithFields,
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