<?php

declare(strict_types=1);

namespace Pgraph\GraphQL;

/**
 * Abstract implementation of an object type definitions.
 */
class MutationType extends QueryType
{
    /**
     * Exception message format for invalid query field argument.
     *
     * @var string
     */
    protected $invalidFieldFormat = '%s is not a valid mutation type field.';

    /**
     * Mutation fields implementation class.
     *
     * @var string
     */
    protected $fieldType = Mutation::class;
}