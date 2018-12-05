<?php

namespace Pgraph\GraphQL\Command;

use Pgraph\Core\Command\AbstractCreateCommand;
use Symfony\Component\Console\Input\InputOption;

class CreateQueryCommand extends AbstractCreateCommand
{
    /**
     * Create handler name.
     *
     * @var string
     */
    protected $name = 'graphql:query';
    
    /**
     * The destination class sub-root namespace.
     * 
     * Note: Backslashes will be trimmed.
     *
     * @return string
     */
    protected function namespace(): string
    {
        return 'GraphQL\Queries';
    }

    /**
     * Get the template filename.
     *
     * @return string
     */
    protected function template(): string
    {
        return __DIR__ . '/templates/query';
    }

    /**
     * Get the template placeholders.
     * 
     * Already provided: 
     * - namespace
     * - sub-namespace
     * - name
     * 
     * Must be an associative array where keys are the placeholders 
     * and values the argument or option name.
     * 
     * Note: to denote a option placeholder, prefixes it with '--'.
     *
     * @return array
     */
    protected function placeholders(): array
    {
        return [
            'return-type' => '--returns'
        ];
    }

    /**
     * Build command arguments as arrays.
     *
     * @return array
     */
    protected function arguments(): array
    {
        return [];
    }

    /**
     * Build command options as arrays.
     *
     * @return array
     */
    protected function options(): array
    {
        return [
            ['returns', 'r', InputOption::VALUE_OPTIONAL, 'Define the query return type', '']
        ];
    }
}
