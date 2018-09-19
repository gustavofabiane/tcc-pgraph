<?php

namespace Framework\Tests\GraphQL\Stubs;

use Framework\GraphQL\EnumType;

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
