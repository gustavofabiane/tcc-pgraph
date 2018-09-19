<?php

namespace Framework\Tests\GraphQL\Stubs;

use Framework\GraphQL\ObjectType;

class StubEnumType extends ObjectType
{
    public function description(): string
    {
        return 'There is a stub description for an object type.';
    }

    public function fields(): iterable
    {
        return [
            'id' => $this->types->id(),
            'name' => $this->types->string(),
            'number' => $this->types->int()
        ];
    }
}
