<?php 

namespace Pgraph\Tests\Core\Command;

use PHPUnit\Framework\TestCase;
use Pgraph\Core\Application;
use Pgraph\Command\Application as ConsoleApplication;
use Pgraph\Tests\Core\Command\Stub\CreateStubCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CreateCommandTest extends TestCase
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

    public function testCreateStubClass()
    {
        $command = $this->console->add(new CreateStubCommand());
        $tester = new CommandTester($command);

        $tester->execute(['name' => 'StubClass']);
        $this->assertContains('App\Stub\StubClass created with success', $tester->getDisplay());

        unlink($this->appDir . '/app/Stub/StubClass.php');
    }

    public function testCreateStubClassForced()
    {
        $command = $this->console->add(new CreateStubCommand());
        $tester = new CommandTester($command);

        $tester->execute(['name' => 'StubClassForced', '--force' => true]);
        $this->assertContains('App\Stub\StubClassForced created with success', $tester->getDisplay());
    }
}
