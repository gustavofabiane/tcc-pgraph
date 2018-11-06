<?php

namespace Framework\Tests\Stubs\Command;

use Framework\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class StubSimpleCommand extends Command
{
    private $name = 'stub-simple';

    protected function configure()
    {
        $this->addArgument('arg', InputArgument::OPTIONAL, '', 'default');
    }

    /**
     * Executes the command
     *
     * @return void
     */
    public function main()
    {
        $this->write('This is a simple command...: ' . $this->arg('arg'));
    }
}
