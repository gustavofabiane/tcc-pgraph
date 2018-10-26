<?php

namespace Framework\Tests\Command;

use Framework\Command\Command;
use Framework\Command\Console;
use PHPUnit\Framework\TestCase;
use Framework\Container\Container;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Tester\CommandTester;
use Framework\Tests\Stubs\Command\StubSimpleCommand;

class CommandTest extends TestCase
{
    /**
     * 
     *
     * @var Container
     */
    protected $container;

    /**
     * 
     *
     * @var Console
     */
    protected $console;

    public function setup()
    {
        $this->container = new Container();
        $this->console = new Console();
        $this->console->setContainer($this->container);
    }

    protected function commander(Command $command): CommandTester
    {
        if (!$this->console->has($command->getName())) {
            $command = $this->console->add($command);
        }
        return new CommandTester($command);
    }

    protected function method(string $methodName): \ReflectionMethod
    {
        $reflected = new \ReflectionMethod(Command::class, $methodName);
        $reflected->setAccessible(true);

        return $reflected;
    }

    public function testInputMethods()
    {
        $command = new class('for-input') extends Command {
            protected function configure()
            {
                $this->addArgument('name');
                $this->addOption('repeat', 'r', InputOption::VALUE_OPTIONAL, '', false);
            }
            public function main()
            {
                $name = $this->arg('name');
                if ($this->opt('repeat') === true) {
                    $name .= $name;
                }
                $this->write($name);
            }
        };

        $argMethod = $this->method('arg');
        $optMethod = $this->method('opt');

        $tester = $this->commander($command);
        $tester->execute(['name' => 'John']);
        
        $this->assertContains('John', $tester->getDisplay());
        $this->assertEquals('John', $argMethod->invoke($command, 'name'));
        $this->assertFalse($optMethod->invoke($command, 'repeat'));
        
        $tester->execute(['name' => 'Jane', '-r' => true]);
        
        $this->assertContains('JaneJane', $tester->getDisplay());
        $this->assertEquals('Jane', $argMethod->invoke($command, 'name'));
        $this->assertTrue($optMethod->invoke($command, 'repeat'));
    }

    public function testMakeSimpleQuestion()
    {
        $inputNameArg = 'Tester Johnson';

        $command = new class('for-ask') extends Command {
            public function main()
            {
                $name = $this->ask('What\'s your name?');
                $this->comment($name);
            }
        };

        $tester = $this->commander($command);
        $tester->setInputs([$inputNameArg]);

        $tester->execute([]);
        $this->assertContains($inputNameArg, $tester->getDisplay(true));
    }

    public function testCallAnotherCommand()
    {
        $command = new class('for-call') extends Command {
            public function main()
            {
                $this->call('stub', ['arg' => 'Called from another command']);
            }
        };
        
        $tester = $this->commander($command);
        $this->console->add(new StubSimpleCommand('stub'));

        $tester->execute([]);

        $this->assertContains('Called from another command', $tester->getDisplay(true));
    }

    public function testAskWithAutoCompletion()
    {
        $command = new class('auto-complete') extends Command {
            public function main()
            {
                $this->setAutoCompleterValues([
                    'one', 'two', 'twelve'
                ]);
                $number = $this->ask('Which number?');
                $this->writeln('Supplied number: ' . $number);
            }
        };

        $tester = $this->commander($command);
        $tester->setInputs(['twelve']);

        $tester->execute([]);
        $this->assertContains('Supplied number: twelve', $tester->getDisplay(true));
    }

    public function testGetCommandDefaultName()
    {
        $customCommand = new class extends Command {
            private $name = 'anonymously';
            public function main()
            {
                ///
            }
        };

        $this->assertEquals('stub-simple', StubSimpleCommand::getDefaultName());
        $this->assertEquals('anonymously', $customCommand::getDefaultName());
    }
}
