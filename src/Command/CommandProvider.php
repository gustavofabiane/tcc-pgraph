<?php

namespace Pgraph\Command;

use Pgraph\Command\Application;
use Pgraph\Core\ProviderInterface;
use Pgraph\Core\Command\ServeCommand;
use Pgraph\Core\Application as Pgraph;
use Pgraph\GraphQL\Command\CreateTypeCommand;
use Pgraph\Core\Command\ValidateSchemaCommand;
use Symfony\Component\Console\Command\Command;
use Pgraph\Core\Command\CreateRequestHandlerCommand;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

class CommandProvider implements ProviderInterface
{
    /**
     * Application instance
     *
     * @var Pgraph
     */
    protected $app;

    /**
     * Registered commands.
     *
     * @var array
     */
    protected $registered = [];

    /**
     * Provide console application and commands.
     *
     * @param Pgraph $app
     * @return void
     */
    public function provide(Pgraph $app)
    {
        $this->app = $app;

        if (!$app->has('console')) {
            $app->register('console', function ($app) {
                $console = new Application();
                $console->setContainer($app);

                return $console;
            });
        }
        $app->alias(Application::class, 'console');

        $app->registerListener('console', function (Application $console, Pgraph $app) {
            $commandLoader = new ContainerCommandLoader(
                $app, $this->generateCommandMap($app)
            );
            $console->setCommandLoader($commandLoader);
        });

        /**
         * Application provided commands.
         */
        $this->register(ServeCommand::class);
        $this->register(CreateRequestHandlerCommand::class);
        $this->register(CreateTypeCommand::class);
        $this->register(ValidateSchemaCommand::class, function ($c) {
            return new ValidateSchemaCommand($c->get('graphqlSchema'));
        });

        /**
         * Register user defined commands.
         */
        $this->commands();
    }

    /**
     * Generate the a application command map for lazy loading.
     *
     * @param Pgraph $app
     * @return array
     */
    protected function generateCommandMap(Pgraph $app): array
    {
        $commandMap = [];
        foreach ($this->registered as $command) {
            $app->register(...$command);
            $commandClass = $command[0];
            $commandMap[$commandClass::getDefaultName()] = $commandClass;
        }
        return $commandMap;
    }

    public function commands(): void
    {
        ///
    }

    /**
     * Register a command in the application.
     *
     * @param string $command
     * @param callable $assembler
     * @return void
     */
    public function register(string $command, callable $assembler = null): void
    {
        $this->registered[] = [$command, $assembler];
    }
}
