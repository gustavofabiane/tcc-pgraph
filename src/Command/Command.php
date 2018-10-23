<?php

namespace Framework\Command;

use Framework\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

abstract class Command extends SymfonyCommand
{
    /**
     * Container implementation instance.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The command input handler.
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * The command output handler.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Get an specific argument from input data.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function arg(string $name, $default = null)
    {
        return $this->input->getArgument($name) ?: $default;
    }

    /**
     * Get the set of present command arguments.
     *
     * @return array
     */
    protected function args(): array
    {
        return $this->input->getArguments();
    }

    /**
     * Get an specific option from input data.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function opt(string $name, $default = null)
    {
        return $this->input->getOption($name) ?: $default;
    }

    /**
     * Get the set of present command options.
     *
     * @return array
     */
    protected function opts(): array
    {
        return $this->input->getOptions();
    }

    /**
     * Echo a given message to the output.
     *
     * @param string $message
     * @param boolean $newLine
     * @param array $options
     * @return void
     */
    protected function echo(string $message, bool $newLine = false, array $options = [])
    {
        $this->output->write($message, $newLine, $options);
    }

    /**
     * Produces a line break in output state.
     *
     * @return void
     */
    protected function breakLine()
    {
        $this->echo('', true);
    }

    /**
     * Run the command setting up input and output before 
     * delegate the process to the parent::run() method.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {        
        $this->input = $input;
        $this->output = $output;

        return parent::run($input, $output);
    }

    /**
     * Set the input and output, execute the handle() method 
     * and updates the output at the end of the execution.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        return $this->container->resolve([$this, 'handle']) ?: null;
    }

    /**
     * Handle the command execution logic with input and output abstraction.
     *
     * @return int|null
     */
    abstract public function handle();

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
