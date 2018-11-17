<?php

namespace Pgraph\Core\Command;

use Pgraph\Command\Command as PgraphCommand;
use Symfony\Component\Console\Input\InputArgument;

abstract class AbstractCreateCommand extends PgraphCommand
{
    /**
     * Configure the create command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the created component.');
        $this->addOption(
            'force', 'f', InputArgument::OPTIONAL, 
            'If the compoenent already exists its forced to the overrided.', false
        );

        foreach ($this->options() as $opt) {
            $this->addOption(...$opt);
        }
        foreach ($this->arguments() as $arg) {
            $this->addArgument(...$arg);
        }
    }

    /**
     * Handle de command process.
     *
     * @return void
     */
    public function main()
    {
        $templatePath = $this->template();
        if (!file_exists($templatePath)) {
            $this->error(sprintf('Given template file path does not exists [%s]', $templatePath));
            return 1;
        }
        
        $namespace = $this->namespace();

        $placeholders = $this->preparedPlaceholders();
        $placeholders['{{name}}'] = ucfirst($this->arg('name'));
        $placeholders['{{namespace}}'] = $this->applicationNamespace();
        $placeholders['{{sub-namespace}}'] = $namespace ? '\\' . $namespace : '';
        
        $template = str_replace(
            array_keys($placeholders), array_values($placeholders), file_get_contents($templatePath)
        );

        $className = $this->fullyQualifiedClassName();

        if (!$this->opt('force') && class_exists($className)) {
            $this->error(sprintf(
                '\'%s\' already exists. Use --force to directly replaces it.', 
                $className
            ));
            return 2;
        }

        if (!is_dir($this->destinationDir())) {
            mkdir($this->destinationDir());
        }
        file_put_contents($this->destinationFile(), $template);

        $this->info(sprintf('%s created with success!', $this->fullyQualifiedClassName()));
        $this->info(sprintf('File: %s', $this->destinationFile()));

        return 0;
    }

    /**
     * Prepare command template placeholders.
     *
     * @return array
     */
    protected function preparedPlaceholders(): array
    {
        $placeholders = [];
        foreach ($this->placeholders() as $placeholder => $var) {
            $placeholders['{{' . $placeholder . '}}'] = strpos($var, '--') !== 0 
                ? $this->arg($var) 
                : $this->opt(substr($var, 0, 2));
        }

        return $placeholders;
    }

    /**
     * Get the application namespace.
     *
     * @return string
     */
    protected function applicationNamespace(): string
    {
        return $this->container->get('config')->get('app', 'app_namespace') ?: 'App';
    }

    /**
     * Get the defined namespace relative path.
     *
     * @return string
     */
    protected function namespacePath(): string
    {
        return str_replace('\\', '/', trim($this->namespace(), '\\/'));
    }

    /**
     * Get the fully qualified class name been created by the command.
     *
     * @return string
     */
    protected function fullyQualifiedClassName(): string
    {
        return $this->applicationNamespace() . '\\' .
               trim($this->namespace(), '\\/') . '\\' . 
               ucfirst($this->arg('name'));
    }

    /**
     * Get the created component destination dir.
     *
     * @return string
     */
    protected function destinationDir(): string
    {
        return $this->container->get('config')->get('app', 'app_dir') . '/app/' . $this->namespacePath();
    }

    /**
     * Get the create component file name with complete path.
     *
     * @return string
     */
    protected function destinationFile(): string
    {
        return $this->destinationDir() . '/' . ucfirst($this->arg('name')) . '.php';
    }

    /**
     * The destination class sub-root namespace.
     * 
     * Note: Backslashes will be trimmed.
     *
     * @return string
     */
    abstract protected function namespace(): string;

    /**
     * Get the template filename.
     *
     * @return string
     */
    abstract protected function template(): string;

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
    abstract protected function placeholders(): array;

    /**
     * Build command arguments as arrays.
     *
     * @return array
     */
    abstract protected function arguments(): array;

    /**
     * Build command options as arrays.
     *
     * @return array
     */
    abstract protected function options(): array;
}
