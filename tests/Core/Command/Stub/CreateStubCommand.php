<?php

namespace Pgraph\Tests\Core\Command\Stub;

use Pgraph\Core\Command\AbstractCreateCommand;

class CreateStubCommand extends AbstractCreateCommand
{
    protected $name = 'create:stub';

    /**
     * The destination class sub-root namespace.
     * 
     * Note: Backslashes will be trimmed.
     *
     * @return string
     */
    protected function namespace(): string
    {
        return 'Stub';
    }

    /**
     * Get the template filename.
     *
     * @return string
     */
    protected function template(): string
    {
        return __DIR__ . '/command-template';
    }

    /**
     * Get the template custom placeholders.
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
        return [];
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
        return [];
    }
}
