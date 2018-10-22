<?php

declare(strict_types=1);

namespace Framework\GraphQL\Definition;

use GraphQL\Type\Definition\FieldDefinition as Definition;
use GraphQL\Type\Definition\FieldArgument;

class FieldDefinition extends Definition
{
    /**
     * Set the field name.
     *
     * @param string $name
     * @return static
     */
    public function name(string $name): self
    {
        $this->config['name'] = $this->name = $name;
        return $this;
    }

    /**
     * Add a new argument to field definition.
     *
     * @param array|FieldArgument $argument
     * @return static
     */
    public function arg($argument): self
    {
        if (! $argument instanceof FieldArgument) {
            $argument = new FieldArgument($argument);
        }
        $this->args[] = $argument;
        $this->config['args'][] = $argument;

        return $this;
    }

    /**
     * Set the field definition description.
     *
     * @param string $description
     * @return static
     */
    public function description(string $description): self
    {
        $this->config['description'] = $this->description = $description;
        return $this;
    }

    /**
     * Set the field deprecation reason.
     *
     * @param string $reason
     * @return static
     */
    public function deprecation(string $reason): self
    {
        $this->config['deprecationReason'] = $this->deprecationReason = $reason;
        return $this;
    }
    
    /**
     * Set the field definition complexity callable.
     *
     * @param callable $complexity
     * @return static
     */
    public function complexity(callable $complexity): self
    {
        $this->config['complexity'] = $this->complexityFn = $complexity;
        return $this;
    }
}