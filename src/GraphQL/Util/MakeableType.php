<?php

declare(strict_types=1);

namespace Framework\GraphQL\Util;

interface MakeableType
{
    public function make(): void;
}