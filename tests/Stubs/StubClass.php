<?php

namespace Framework\Tests\Stubs;

class StubClass implements StubInterface
{
    public function method()
    {
        return "Implemented";
    }

    public function toResolve(int $number, $userDefinedParam)
    {
        return $number + $userDefinedParam;
    }

    public function toResolveDefault(int $number = 2, string $code = '400'): int
    {
        return $number * $code;
    }
}
