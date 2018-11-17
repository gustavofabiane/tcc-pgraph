<?php

namespace Pgraph\Core\Command;

use Pgraph\Command\Command as PgraphCommand;
use Symfony\Component\Console\Input\InputArgument;

class ServeCommand extends PgraphCommand
{
    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('host', InputArgument::OPTIONAL, 'The hostname to serve for.', 'localhost')
             ->addArgument('port', InputArgument::OPTIONAL, 'The hostname port to listen.', '8080');
    }

    /**
     * Start the serve process.
     *
     * @return void
     */
    public function main()
    {
        chdir($this->container->get('config')->get('public_dir'));

        $this->info('Starting P.graph development server... ');
        $this->info(sprintf('Serving at: %s:%s', $this->arg('host'), $this->arg('port')));

        passthru($this->serve(), $status);

        return $status;
    }

    /**
     * Get the formatted PHP serve command.
     *
     * @return string
     */
    protected function serve(): string
    {
        return sprintf(
            'php -S %s:%s index.php', 
            $this->arg('host'), 
            $this->arg('port')
        );
    }
}
