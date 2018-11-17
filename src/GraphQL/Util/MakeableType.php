<?php

declare(strict_types=1);

namespace Pgraph\GraphQL\Util;

interface MakeableType
{
    public function make(): void;
}