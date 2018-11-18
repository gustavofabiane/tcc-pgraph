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
        $this->info('Starting P.graph development server... ');
        
        chdir(sprintf('"%s"', $this->container->get('config')->get('public_dir')));
        passthru($this->serve(), $status);

        $this->info(sprintf('Serving at: http://%s:%s', $this->arg('host'), $this->arg('port')));
        
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
            'php -S %s:%s -t ./', 
            $this->arg('host'), 
            $this->arg('port')
        );
    }
}
