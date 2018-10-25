<?php

namespace Framework\Tests\Stubs\Command;

use Framework\Command\Command;

class StubSimpleCommand extends Command
{
    /**
     * Executes the command
     *
     * @return void
     */
    public function main()
    {
        $this->write('This is a simple command...');
    }
}
