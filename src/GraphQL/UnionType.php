<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use Framework\GraphQL\Util\TypeTrait;
use GraphQL\Type\Definition\UnionType as BaseUnionType;

/**
 * Abstract implementation of an union type definitions.
 */
abstract class UnionType extends BaseUnionType
{
    use TypeTrait;

    /**
     * Make base union type from implemented library.
     *
     * @return void
     */
    public final function make()
    {
        if (!$this->config) {
            parent::__construct([
                'description' => $this->description(),
                'types' => [$this, 'types'],
                'resolveType' => [$this, 'resolveType'] 
                // -------> MUST overide resolveType() to bypass this parameter
            ]);
            $this->setInstance($this);
        }
    }

    /**
     * Must return the list of types that the union provide.
     *
     * @return array
     */
    abstract public function types(): array;

    /**
     * The union type description.
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description = sprintf(
            'An union type defined as \'%s\'', $this->name()
        );
    }
}