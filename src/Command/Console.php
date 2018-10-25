<?php

namespace Framework\Command;

use Symfony\Component\Console\Application;
use Framework\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class Console extends Application
{
    /**
     * Container implementation instance.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * A
     *
     * @param Command $command
     * @return void
     */
    public function add(SymfonyCommand $command): SymfonyCommand
    {
        if ($command instanceof Command) {
            $command->setContainer($this->container);
        }
        return parent::add($command);
    }

    /**
     * Set console container instance.
     *
     * @param ContainerInterface $container
     * @return static
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Get console container instance.
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
