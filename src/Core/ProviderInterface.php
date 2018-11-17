<?php

namespace Pgraph\Core;

interface ProviderInterface
{
    public function provide(Application $app);
}