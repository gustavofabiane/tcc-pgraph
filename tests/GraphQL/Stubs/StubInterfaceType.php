<?php

namespace Framework\Tests\GraphQL\Stubs;

use Framework\GraphQL\InterfaceType;

class StubInterfaceType extends InterfaceType
{
    public function description(): string
    {
        return 'There is a stub description for an interface type.';
    }

    public function fields(): array
    {
        return [
            'name' => $this->registry->string(),
        ];
    }
}
