<?php

namespace Pgraph\Core\Command;

class CreateRequestHandlerCommand extends AbstractCreateCommand
{
    /**
     * Create handler name.
     *
     * @var string
     */
    protected $name = 'create:handler';
    
    /**
     * The destination class sub-root namespace.
     * 
     * Note: Backslashes will be trimmed.
     *
     * @return string
     */
    protected function namespace(): string
    {
        return 'Http\Handlers';
    }

    /**
     * Get the template filename.
     *
     * @return string
     */
    protected function template(): string
    {
        return __DIR__ . '/templates/request-handler';
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
        return [];
    }
}
