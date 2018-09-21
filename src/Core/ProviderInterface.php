<?php

namespace Framework\Core;

interface ProviderInterface
{
    public function provide(Application $app);
}