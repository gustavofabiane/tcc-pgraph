<?php

declare(strict_types=1);

namespace Pgraph\GraphQL;

use GraphQL\Type\Definition\Type;

use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\BooleanType;

interface TypeRegistryInterface
{
    public function addType($type, string $name = null);
    public function addField($field, string $name = null);
    public function exists(string $entry): bool;
    public function keyForType($type): string;
    public function type(string $type): Type;
    public function field(
        string $field, string $withName = null, 
        string $withKey = null, string $withDeprecationReason = null
    ): Field;

    public function id(): IDType;
    public function int(): IntType;
    public function float(): FloatType;
    public function string(): StringType;
    public function boolean(): BooleanType;
}