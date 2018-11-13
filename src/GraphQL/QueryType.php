<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\FieldDefinition;

/**
 * Abstract implementation of an object type definitions.
 */
class QueryType extends ObjectType
{
    /**
     * The schema defined queries.
     *
     * @var array
     */
    protected $queries = [];

    /**
     * Exception message format for invalid query field argument.
     *
     * @var string
     */
    protected $invalidFieldFormat = '%s is not a valid query type field.';

    /**
     * Query fields implementation class.
     *
     * @var string
     */
    protected $fieldType = Query::class;

    /**
     * Get the query type defined fields.
     *
     * @return array
     */
    public function fields(): array
    {
        $queries = [];
        foreach ($this->queries as $name => $query) {
            if (is_string($query) && is_subclass_of($query, $this->fieldType)) {
                $query = (new $query($this->registry))->make($name)->toFieldDefnition();
                $queries[$name] = $query;
            } else {
                $query = FieldDefinition::create($query + compact('name'));
                $queries[$name] = $query;
            }

            $this->queries[$name] = $query;
        }
        return $queries;
    }

    /**
     * Resolve query fields using its resolvers.
     *
     * @param mixed $src
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolveField(
        $src, 
        array $args = [], 
        $context = null, 
        ResolveInfo $info = null
    ) {
        return call_user_func(
            $this->queries[$info->fieldName]->resolveFn,
            $src, $args, $context, $info
        );
    }

    /**
     * Set the query/mutation type fields.
     *
     * @param array $queries
     * @return static
     */
    public function setQueries(array $queries): self
    {
        foreach($queries as $name => $query) {
            if (is_string($query) && !is_subclass_of($query, $this->fieldType)) {
                throw new \InvalidArgumentException(sprintf(
                    $this->invalidFieldFormat, $query
                ));
            }
            $this->queries[$name] = $query;
        }
        return $this;
    }

    /**
     * Create a query type from array of fields.
     *
     * @param array $fields
     * @param TypeRegistryInterface $registry
     * @return static
     */
    public static function createFromFields(array $fields, TypeRegistryInterface $registry = null): self
    {
        $queryType = new static();
        $queryType->setTypeRegistry($registry)
                  ->setQueries($fields)
                  ->make();
        
        return $queryType;
    }
}
