<?php

namespace Pgraph\Core\Command;

use GraphQL\Type\Schema;
use Pgraph\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class ValidateSchemaCommand extends Command
{
    /**
     * Schema validator name.
     *
     * @var string
     */
    protected $name = 'grahpql:validate';

    /**
     * Validates the application GraphQL schema.
     *
     * @return void
     */
    public function main(Schema $graphqlSchema)
    {
        try {
            $graphqlSchema->assertValid();
            $this->info('Current GraphQL schema is valid!');
        } catch (GraphQL\Error\InvariantViolation $e) {
            $this->error('Oops! Your current GraphQL schema is invalid!');
            $this->error('Assertion Message: ' . $e->getMessage());
        }
    }
}
