<?php 

namespace Pgraph\Tests\Core\Command;

use PHPUnit\Framework\TestCase;
use Pgraph\Tests\Core\Command\Stub\CreateStubCommand;

class CreateCommandTest extends TestCase
{
    use CommandTestTrait;

    protected $command = CreateStubCommand::class;

    public function testCreateStubClass()
    {
        $tester = $this->commandTester();

        $tester->execute(['name' => 'StubClass']);
        $this->assertContains('App\Stub\StubClass created with success', $tester->getDisplay());

        unlink($this->appDir . '/app/Stub/StubClass.php');
        $this->assertFileNotExists($this->appDir . '/app/Stub/StubClass.php');
    }

    public function testCreateStubClassForced()
    {
        file_put_contents($this->appDir . '/app/Stub/StubClassForced.php', 'stub-file-test');

        $tester = $this->commandTester();

        $tester->execute(['name' => 'StubClassForced', '--force' => true]);
        $this->assertContains('App\Stub\StubClassForced created with success', $tester->getDisplay());

        unlink($this->appDir . '/app/Stub/StubClassForced.php');
        $this->assertFileNotExists($this->appDir . '/app/Stub/StubClassForced.php');
    }

    public function testCreateWithConstructor()
    {
        $tester = $this->commandTester();

        $tester->execute(['name' => 'StubClassWithConstructor', '--constructor' => true]);
        $this->assertContains('App\Stub\StubClassWithConstructor created with success', $tester->getDisplay());

        unlink($this->appDir . '/app/Stub/StubClassWithConstructor.php');
        $this->assertFileNotExists($this->appDir . '/app/Stub/StubClassWithConstructor.php');
    }
}
