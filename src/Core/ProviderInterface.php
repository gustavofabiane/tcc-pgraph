<?php

namespace Pgraph\Core;

/**
 * Define the servide provider behavior accepted 
 * by the core application's addProvider() method.
 */
interface ProviderInterface
{
    /**
     * Provide a set of service entries to the given Application instance.
     *
     * @param Application $app
     * @return void
     */
    public function provide(Application $app);
}