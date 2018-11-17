<?php

declare(strict_types=1);

namespace Pgraph\GraphQL;

use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;

class SchemaFactory
{
    /**
     * Application type registry.
     *
     * @var TypeRegistryInterface
     */
    protected $registry;

    /**
     * Createa new schema factory instance.
     *
     * @param TypeRegistryInterface $registry
     */
    public function __construct(TypeRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Create a GraphQL instance from given parameters.
     *
     * @param array|QueryType $query
     * @param array|MutationType $mutation
     * @param array $directives
     * @param array $types
     * @param callable $typeLoader
     * @return Schema
     */
    public function create(
        $query, 
        $mutation = null,
        array $directives = [],
        array $types = [],
        callable $typeLoader = null
    ): Schema {
        $schemaConfig = SchemaConfig::create();
        
        if ($query) {
            if (! $query instanceof QueryType && ! $query instanceof ObjectType) {
                $query = QueryType::createFromFields($query, $this->registry); 
            }
            $schemaConfig->setQuery($query);
        }
        if ($mutation) {
            if (! $mutation instanceof MutationType && ! $query instanceof ObjectType) {
                $mutation = MutationType::createFromFields($mutation, $this->registry); 
            }
            $schemaConfig->setMutation($mutation);
        }
        if ($types) {
            $schemaConfig->setTypes($types);
        }
        if ($directives) {
            $schemaConfig->setDirectives($directives);
        }
        if ($typeLoader) {
            $schemaConfig->setTypeLoader($typeLoader);
        }

        return $this->createFromSchemaConfig($schemaConfig);
    }

    /**
     * Create a new schema instance from a schema configuration.
     *
     * @param SchemaConfig $config
     * @return Schema
     */
    public function createFromSchemaConfig(SchemaConfig $config): Schema
    {
        return new Schema($config);
    }
}
