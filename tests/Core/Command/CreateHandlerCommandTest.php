<?php

namespace Pgraph\Tests\Core\Command;

use PHPUnit\Framework\TestCase;
use Pgraph\Core\Command\CreateRequestHandlerCommand;

class CreateHandlerCommandTest extends TestCase
{
    use CommandTestTrait;

    protected $command = CreateRequestHandlerCommand::class;

    public function testCreateHandler()
    {
        $tester = $this->commandTester();

        $tester->execute(['name' => 'StubRequestHandler', '--force' => true, '--constructor' => true]);
        $this->assertContains('App\Http\Handlers\StubRequestHandler created with success', $tester->getDisplay());
    }
}
