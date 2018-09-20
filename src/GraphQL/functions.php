<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use GraphQL\Type\Definition\Type;
use Framework\GraphQL\TypeRegistry;

/**
 * Get type from the type registry.
 *
 * @param string|Type $type
 * @return Type
 */
function type($type): Type
{
    if ($type instanceof Type) {
        return $type;
    }
    return TypeRegistry::getInstance()->type($type);
}

/**
 * Resolve parameters to a valid type field.
 *
 * @param string|Type $type
 * @param string $name
 * @param array $args
 * @param callable $resolve
 * @param mixed $defaultValue
 * @param string $description
 * @param string $deprecationReason
 * @param callable $complexity
 * @return iterable|\Framework\GraphQL\Field
 */
function field(
    $type,
    $name,
    array $args = [],
    callable $resolve = null,
    $defaultValue = null,
    string $description = null,
    string $deprecationReason = null,
    callable $complexity = null
): iterable {

    if (count(func_get_args()) === 2 && 
        TypeRegistry::getInstance()->exists($type)
    ) {
        $field = TypeRegistry::getInstance()->field($type, $name);
    } else {
        $field = compact(
            'name', 'type', 'args', 'resolve',
            'defaultValue', 'description', 
            'deprecationReason', 'complexity'
        );
        if (! $type instanceof Type) {
            $field['type'] = TypeRegistry::getInstance()->type($type);
        }
    }

    return $field;
}

/**
 * Resolve parameters to field argument structure.
 *
 * @param string $name
 * @param string|Type $type
 * @param mixed $defaultValue
 * @param string $description
 * @return iterable
 */
function argument(string $name, $type, $defaultValue = null, string $description = null): iterable
{
    return [
        'name' => $name,
        'type' => $type instanceof Type ? $type : TypeRegistry::getInstance()->type($type),
        'default_value' => $defaultValue,
        'description' => $description
    ];
}

/**
 * Resolve given parameters as a enum value entry
 *
 * @param string $name
 * @param mixed $value
 * @param string $description
 * @param string $deprecationReason
 * @return array
 */
function enumValue(
    string $name,
    $value = null,
    string $description = null,
    string $deprecationReason = null
): array {
    $name = str_replace(' ', '_', strtoupper($name));

    $enumValue = compact('name', 'value');
    if (!$value) {
        $enumValue['value'] = $name;
    }
    if ($description) {
        $enumValue['description'] = $description;
    }
    if ($deprecationReason) {
        $enumValue['deprecationReason'] = $deprecationReason;
    }

    return $enumValue;
}