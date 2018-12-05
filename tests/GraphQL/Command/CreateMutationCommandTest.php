<?php

namespace Pgraph\Tests\GraphQL\Command;

use PHPUnit\Framework\TestCase;
use Pgraph\GraphQL\Command\CreateMutationCommand;
use Pgraph\Tests\Core\Command\CommandTestTrait;

class CreateMutationCommandTest extends TestCase
{
    use CommandTestTrait;

    protected $command = CreateMutationCommand::class;

    public function testCreateQuery()
    {
        $tester = $this->commandTester();

        $tester->execute(['name' => 'VariousUpdates', '--returns' => 'stub',  '--force' => true]);
        $this->assertContains('App\GraphQL\Mutations\VariousUpdates created with success', $tester->getDisplay());
    }
}
