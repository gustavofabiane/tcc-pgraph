<?php

namespace Pgraph\Tests\Stubs;

class Stub2Class
{
    protected $stub;

    public function __construct(StubClass $stub)
    {
        $this->stub = $stub;
    }

    public function getStub()
    {
        return $this->stub;
    }
}
