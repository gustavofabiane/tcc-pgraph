<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use Framework\GraphQL\Util\TypeWithFields;
use GraphQL\Type\Definition\FieldDefinition;
use Framework\GraphQL\Util\ImplementsInterface;

class Fields
{
    /**
     * Fields' type
     *
     * @var TypeWithFields
     */
    protected $type;

    /**
     * Create a new fields instance.
     *
     * @param TypeWithFields $type
     */
    public function __construct(TypeWithFields $type)
    {
        $this->type = $type;
    }

    /**
     * Make fields callable to resolve for type.
     *
     * @return array
     */
    public function __invoke(): array
    {
        $mappedFields = [];
        foreach ($this->aggregatedFields() as $fieldKey => $fieldData) {
            if ($fieldData instanceof Field) {
                $mappedFields[] = $fieldData;
                continue;
            }
            if ($fieldData instanceof FieldDefinition) {
                /** @var \GraphQL\Type\Definition\FieldDefinition $fieldData */
                if (!$fieldData->resolveFn && ($fieldResolver = $this->type->getFieldResolver($fieldData->name))) {
                    $fieldData->config['resolve'] = $fieldData->resolveFn = $fieldResolver;
                }
                $mappedFields[] = $fieldData;
                continue;
            }
            $field = [];
            if (is_string($fieldKey)) {
                $field['name'] = $fieldKey;
            } else {
                $field['name'] = $fieldData instanceof \Traversable ? $fieldData['name'] : null;
            }
            if ($fieldData instanceof \Traversable) {
                $field += $fieldData;
            } else {
                $field['type'] = $fieldData;
            }
            if (!isset($field['resolve']) || !is_callable($field['resolve'])) {
                if ($fieldResolver = $this->type->getFieldResolver($field['name'])) {
                    $field['resolve'] = $fieldResolver;
                }
            }
            $mappedFields[] = $field;
        }
        return $mappedFields;
    }

    /**
     * Aggregate the type fields with the fields defined by the interfaces implemented by the type.
     *
     * @return iterable
     */
    protected function aggregatedFields(): iterable
    {
        $fields = $this->type->fields();
        if ($this->type instanceof ImplementsInterface) {
            /** @var \GraphQL\Type\Definition\InterfaceType[] $implemented */
            $implemented = $this->type->implements();
            foreach ($implemented as $interface) {
                $interfaceFields = $interface->getFields();
                foreach ($interfaceFields as $interfaceField) {
                    if (!in_array($interfaceField->name, array_keys($fields) + array_column($fields, 'name'))) {
                        $fields[] = $interfaceField;
                    }
                }
            }
        }
        return $fields;
    }

    /**
     * Create a new instance with static call.
     *
     * @param TypeWithFields $type
     * @return static
     */
    public static function create(TypeWithFields $type): self
    {
        return new static($type);
    }
}