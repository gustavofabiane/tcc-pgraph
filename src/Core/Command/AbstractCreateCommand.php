<?php

namespace Pgraph\Core\Command;

use Pgraph\Command\Command as PgraphCommand;
use Symfony\Component\Console\Input\InputOption;
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
            'force', 'f', InputOption::VALUE_NONE, 
            'If the component already exists its forced to the overrided.'
        );
        $this->addOption(
            'constructor', 'c', InputOption::VALUE_NONE,
            'Creates the new class with its constructor already defined.'
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
        $className = $this->fullyQualifiedClassName();
        $templatePath = $this->template();
        $destinationDir = $this->destinationDir();
        $destinationFile = $this->destinationFile();

        if (!file_exists($templatePath)) {
            $this->error(sprintf('Given template file path does not exists [%s]', $templatePath));
            return 1;
        }
        
        if ($this->opt('force') === false && file_exists($destinationFile)) {
            $this->error(sprintf(
                '\'%s\' already exists. Use --force to directly replaces it.', 
                $className
            ));
            return 2;
        }

        $name = $this->name();
        $namespace = $this->fullNamespace();

        $placeholders = $this->preparedPlaceholders();
        $placeholders['{{name}}'] = $name;
        $placeholders['{{namespace}}'] = $namespace;
        
        $template = file_get_contents($templatePath);

        $constructor = $this->opt('constructor') === true ? $this->constructor() : '';
        $template = str_replace('{{constructor}}', $constructor, $template);

        $template = str_replace(
            array_keys($placeholders), array_values($placeholders), $template
        );

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, '0755', true);
        }
        file_put_contents($destinationFile, $template);

        $this->info(sprintf('%s created with success!', $className));
        $this->info(sprintf('File: %s', $destinationFile));

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
        return str_replace(
            [$this->applicationNamespace() . '\\', '\\'], 
            ['', '/'], 
            $this->fullNamespace()
        );
    }

    protected function fullNamespace(): string
    {
        $namespace = trim($this->namespace(), '\\/');
        $customNamespace = $this->customNamespace();

        $namespace .= $customNamespace ? '\\' . $customNamespace : '';

        return $this->applicationNamespace() . '\\' . $namespace;
    }

    /**
     * Get the fully qualified class name been created by the command.
     *
     * @return string
     */
    protected function fullyQualifiedClassName(): string
    {
        return $this->fullNamespace() . '\\' . $this->name();
    }

    protected function customNamespace(): string
    {
        $namespace = '';
        $argument = $this->arg('name');
        $namePos = strrchr($argument, '\\');
        if ($namePos !== false) {
            $namespace = substr($argument, 0, $namePos);
        }
        return trim($namespace, '\\/');
    }

    protected function name(): string
    {
        $argument = $this->arg('name');
        $nameParts = explode('\\', $argument);
        
        return ucfirst(end($nameParts));
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

    protected function constructor(): string
    {
        return <<<EOF
/**
     * Create a new {{name}} instance.
     */
    public function __construct()
    {
        ///
    }
EOF;
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
