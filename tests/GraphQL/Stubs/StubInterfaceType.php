<?php

namespace Pgraph\Tests\GraphQL\Stubs;

use Pgraph\GraphQL\InterfaceType;

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
