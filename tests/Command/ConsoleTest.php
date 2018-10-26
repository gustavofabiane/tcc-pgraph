<?php

namespace Framework\Tests\Command;

use Framework\Command\Console;
use PHPUnit\Framework\TestCase;
use Framework\Core\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application as SymfonyApplication;
use Framework\Tests\Stubs\Command\StubSimpleCommand;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

class ConsoleTest extends TestCase
{
    /**
     * Test create a terminal instance.
     *
     * @return Console
     */
    public function testCreateInstance()
    {
        $console = new Console();
        $this->assertInstanceOf(Console::class, $console);
        $this->assertInstanceOf(SymfonyApplication::class, $console);

        $console->setContainer($app = new Application());
        $this->assertSame($app, $console->getContainer());

        return $console;
    }

    /**
     * @depends testCreateInstance
     *
     * @param Console $console
     * @return void
     */
    public function testExecuteSimpleCommand(Console $console)
    {
        $command = new Command('simple');
        $command->addArgument('name', InputArgument::REQUIRED)
                ->addArgument('times', InputArgument::OPTIONAL, '', 5);

        $command->setCode(function ($in, $out) {
            for ($i=0; $i < $in->getArgument('times'); $i++) { 
                $out->write($in->getArgument('name'));
            }
        });

        $console->add($command);
        $this->assertSame($command, $console->find('simple'));

        $tester = new CommandTester($console->find('simple'));
        $tester->execute(['name' => 'testing', 'times' => 2]);

        $this->assertContains('testingtesting', $tester->getDisplay(true));
    }

    /**
     * @depends testCreateInstance
     *
     * @param Console $console
     * @return void
     */
    public function textExecuteStubSimpleCommand(Console $console)
    {
        $stubCommand = new StubSimpleCommand('stub');
        $console->add($stubCommand);

        $tester = new CommandTester($console->find('stub'));
        $tester->execute(['arg' => 'testing']);

        $this->assertContains(
            'This is a simple command...: testing', 
            $tester->getDisplay(true)
        );
    }

    /**
     * @depends testCreateInstance
     *
     * @param Console $console
     * @return void
     */
    public function testLazyLoadCommands(Console $console)
    {
        $container = $console->getContainer();
        $container->singleton(StubSimpleCommand::class, null, ['name' => 'stub']);

        $loader = new ContainerCommandLoader($console->getContainer(), [
            'stub' => StubSimpleCommand::class
        ]);
        $console->setCommandLoader($loader);

        $this->assertInstanceOf(StubSimpleCommand::class, $console->find('stub'));
    }
}
