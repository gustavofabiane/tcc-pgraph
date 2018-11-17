<?php

namespace Pgraph\Tests\GraphQL\Stubs;

use Pgraph\GraphQL\EnumType;

class StubEnumType extends EnumType
{
    public function description(): string
    {
        return 'There is a stub description.';
    }

    public function values(): array
    {
        return ['ONE', 'TWO', 'THREE'];
    }
}
