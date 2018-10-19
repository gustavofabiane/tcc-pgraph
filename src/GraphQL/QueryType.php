<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use GraphQL\Type\Definition\Type;
use Framework\GraphQL\Util\TypeTrait;
use Framework\GraphQL\Util\TypeWithFields;

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
     * Get the query type defined fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return $this->queries;
    }

    public function addField($type, string $name): self
    {
        if (! $type instanceof Type) {
            $type = $this->checkTypeInRegistry($type);
        }
        if (!$name) {
            $name = $type->name;
        }
        $this->queries[$name] = $type;

        if (!$this->registry->exists($type->name)) {
            $this->registry->addType($type, $type->name);
        }
        
        return $this;
    }

    /**
     * Check if the type exists in registry.
     *
     * @param string $type
     * @return Type
     */
    protected function checkTypeInRregistry(string $type)
    {
        $typeName = $this->registry->keyForType($type);
        if (!$this->registry->exists($typeName)) {
            $typeName = $this->registry->addType($type, $typeName);
        }
        return $this->registry->type($typeName);
    }

    public function resolve($src, $args, $context, $info)
    {
        return [
            'sum' => 12345
        ];
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
        $queryType->setTypeRegistry($registry);

        foreach ($fields as $name => $fieldType) {
            $queryType->addField($fieldType, $name);
        }

        $queryType->make();
        return $queryType;
    }
}