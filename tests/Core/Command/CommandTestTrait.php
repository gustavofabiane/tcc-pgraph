<?php

namespace Pgraph\Tests\Core\Command;

use Pgraph\Core\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Pgraph\Command\Application as ConsoleApplication;

/**
 * 
 */
trait CommandTestTrait
{
    protected $appDir = __DIR__ . '/../../utils';
    protected $appNamespace = 'App';

    /**
     * The framework core application.
     *
     * @var Application
     */
    protected $application;

    /**
     * The console application.
     *
     * @var ConsoleApplication
     */
    protected $console;

    public function setup()
    {
        $this->application = new Application();
        $this->application['config']->set('app', [
            'app_dir' => $this->appDir,
            'app_namespace' => $this->appNamespace
        ]);
        
        $this->console = new ConsoleApplication();
        $this->console->setContainer($this->application);
    }

    protected function commandTester(): CommandTester
    {
        $commandClass = $this->command;
        $command = $this->console->add(new $commandClass());
        return new CommandTester($command);
    }
}
