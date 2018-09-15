<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use Framework\GraphQL\Field;
use GraphQL\Type\Definition\Type;

interface TypeRegistryInterface
{
    public function setType(Type $type);
    public function setField(Field $field);

    public function type(string $type): Type;
    public function field(string $field): Field;
}