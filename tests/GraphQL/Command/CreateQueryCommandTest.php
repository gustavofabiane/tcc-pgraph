<?php

namespace Pgraph\Tests\GraphQL\Command;

use PHPUnit\Framework\TestCase;
use Pgraph\GraphQL\Command\CreateQueryCommand;
use Pgraph\Tests\Core\Command\CommandTestTrait;

class CreateQueryCommandTest extends TestCase
{
    use CommandTestTrait;

    protected $command = CreateQueryCommand::class;

    public function testCreateQuery()
    {
        $tester = $this->commandTester();

        $tester->execute(['name' => 'VariousObjects', '--returns' => 'stub',  '--force' => true]);
        $this->assertContains('App\GraphQL\Queries\VariousObjects created with success', $tester->getDisplay());
    }
}
