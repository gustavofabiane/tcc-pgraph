<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use GraphQL\Type\Definition\Type;
use Framework\GraphQL\TypeRegistry;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;

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
 * Wrap the given type into a ListOf type.
 *
 * @param string|Type $type
 * @return ListOfType
 */
function listOf($type): ListOfType
{
    return TypeRegistry::getInstance()->listOf($type);
}

/**
 * Wrap the given type into a NonNull type.
 *
 * @param string|Type $type
 * @return NonNull
 */
function nonNull($type): NonNull
{
    return TypeRegistry::getInstance()->nonNull($type);
}

/**
 * Resolve parameters to a valid type field.
 *
 * @param string|Type $type
 * @param string $name
 * @param array $args
 * @param callable $resolve
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
            'name',
            'type',
            'args',
            'resolve',
            'description',
            'deprecationReason',
            'complexity'
        );
        if (! $type instanceof Type) {
            $field['type'] = type($type);
        }
    }

    return $field;
}

/**
 * Resolve parameters to a valid input type field.
 *
 * @param string $name
 * @param string|Type $type
 * @param string $description
 * @param mixed $defaultValue
 * @return array
 */
function inputField(string $name, $type, string $description = null, $defaultValue = null): array
{
    $field = compact('name', 'type', 'description');
    if (! $type instanceof Type) {
        $field['type'] = type($type);
    }
    if ($defaultValue) {
        $field['defaultValue'] = $defaultValue;
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
    $argument = [
        'name' => $name,
        'type' => $type instanceof Type ? $type : type($type),
        'description' => $description
    ];
    if ($defaultValue) {
        $argument['defaultValue'] = $defaultValue;
    }
    return $argument;
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
