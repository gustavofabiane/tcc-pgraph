<?php

namespace Pgraph\Tests\GraphQL\Stubs;

use Pgraph\GraphQL\UnionType;
use GraphQL\Type\Definition\ResolveInfo;

class StubUnionType extends UnionType
{
    public function description(): string
    {
        return 'There is a stub description for an union type.';
    }

    public function types(): array
    {
        return [
            $this->registry->type('StubObject'),
            $this->registry->type('StubObjectTwo')
        ];
    }

    public function resolveType($objectValue, $context, ResolveInfo $info)
    {
        if ($objectValue->type == 'one') {
            return $this->registry->type('StubObject');
        }
        return $this->registry->type('StubObjectTwo');
    }
}
