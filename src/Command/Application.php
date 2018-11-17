<?php

namespace Pgraph\Command;

use Symfony\Component\Console\Application as Symfony;
use Pgraph\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class Application extends Symfony
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
