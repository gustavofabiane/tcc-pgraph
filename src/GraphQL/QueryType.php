<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;

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

    /**
     * Resolve a root type field either if no field resolver is defined.
     *
     * Note: If the field is not resolvable the root value or the context will be returned.
     *
     * @param mixed $source
     * @param array $args
     * @param \Framework\Core\Application $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolveField($source, array $args = [], $context = null, ResolveInfo $info)
    {
        $fieldName = $info->fieldName;

        $field = $this->getField($fieldName);
        $fieldType = $field->getType();

        $resolver = null;

        if ($field->resolveFn) {
            $resolver = $field->resolveFn;
        } elseif (method_exists($fieldType, 'resolve')) {
            $resolver = [$fieldType, 'resolve'];
        }
        
        if ($resolver) {
            return call_user_func($resolver, $source, $args, $context, $info);
        }
        
        return $source ?: $context;
    }

    /**
     * Add a new field to query type.
     *
     * @param Type|string $type
     * @param string $name
     * @return static
     */
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
            $this->registry->addType($type);
        }
        
        return $this;
    }

    /**
     * Check if the type exists in registry.
     *
     * @param string $type
     * @return Type
     */
    protected function checkTypeInRegistry(string $type): Type
    {
        $typeName = $this->registry->keyForType($type);
        if (!$this->registry->exists($typeName)) {
            $typeName = $this->registry->addType($type, $typeName);
        }
        return $this->registry->type($typeName);
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
