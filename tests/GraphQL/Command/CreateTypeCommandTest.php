<?php

namespace Pgraph\Tests\GraphQL\Command;

use PHPUnit\Framework\TestCase;
use Pgraph\GraphQL\Command\CreateTypeCommand;
use Pgraph\Tests\Core\Command\CommandTestTrait;

class CreateHandlerCommandTest extends TestCase
{
    use CommandTestTrait;

    protected $command = CreateTypeCommand::class;

    public function testCreateObjectType()
    {
        $tester = $this->commandTester();

        $tester->execute(['name' => 'Stub', '--type' => 'object',  '--force' => true, '--constructor' => true]);
        $this->assertContains('App\GraphQL\Types\Stub created with success', $tester->getDisplay());
    }

    public function testCreateInputObjectType()
    {
        $tester = $this->commandTester();

        $tester->execute(['name' => 'InputStub', '--type' => 'input',  '--force' => true, '--constructor' => true]);
        $this->assertContains('App\GraphQL\Types\InputStub created with success', $tester->getDisplay());
    }

    public function testCreateUnionType()
    {
        $tester = $this->commandTester();

        $tester->execute(['name' => 'DumbUnion', '--type' => 'union',  '--force' => true, '--constructor' => true]);
        $this->assertContains('App\GraphQL\Types\DumbUnion created with success', $tester->getDisplay());
    }
}
