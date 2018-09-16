<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use GraphQL\Type\Definition\Type;

interface TypeRegistryInterface
{
    public function addType($type, string $name = null);
    public function addField($field, string $name = null);

    public function type(string $type): Type;
    public function field(string $field, string $withName = null): Field;
}