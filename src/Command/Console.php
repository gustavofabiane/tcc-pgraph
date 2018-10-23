<?php

namespace Framework\Command;

use Symfony\Component\Console\Application;
use Framework\Container\ContainerInterface;

class Console extends Application
{
    /**
     * Container implementation instance.
     *
     * @var ContainerInterface
     */
    protected $container;

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
