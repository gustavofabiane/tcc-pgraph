<?php

namespace Pgraph\GraphQL\Command;

class CreateMutationCommand extends CreateQueryCommand
{
    /**
     * Create handler name.
     *
     * @var string
     */
    protected $name = 'graphql:mutation';
    
    /**
     * The destination class sub-root namespace.
     * 
     * Note: Backslashes will be trimmed.
     *
     * @return string
     */
    protected function namespace(): string
    {
        return 'GraphQL\Mutations';
    }

    /**
     * Get the template filename.
     *
     * @return string
     */
    protected function template(): string
    {
        return __DIR__ . '/templates/mutation';
    }
}
