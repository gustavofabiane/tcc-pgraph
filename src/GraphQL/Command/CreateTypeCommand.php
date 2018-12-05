<?php

namespace Pgraph\GraphQL\Command;

use Pgraph\Core\Command\AbstractCreateCommand;
use Symfony\Component\Console\Input\InputOption;

class CreateTypeCommand extends AbstractCreateCommand
{
    /**
     * Create handler name.
     *
     * @var string
     */
    protected $name = 'graphql:type';
    
    /**
     * The destination class sub-root namespace.
     * 
     * Note: Backslashes will be trimmed.
     *
     * @return string
     */
    protected function namespace(): string
    {
        return 'GraphQL\Types';
    }

    /**
     * Get the template filename.
     *
     * @return string
     */
    protected function template(): string
    {
        $type = $this->opt('type');
        if (!in_array($type, ['object', 'scalar', 'input', 'enum', 'interface', 'union'])) {
            throw new \InvalidArgumentException(sprintf('[%s] is not a valid GraphQL type identifier', $type));
        }
        return __DIR__ . sprintf('/templates/types/%s', $type);
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
            ['type', 't', InputOption::VALUE_REQUIRED, 'Define the GraphQL base type to the created type']
        ];
    }
}
