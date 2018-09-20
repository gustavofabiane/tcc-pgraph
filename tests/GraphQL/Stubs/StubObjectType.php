<?php

declare(strict_types=1);

namespace Framework\Tests\GraphQL\Stubs;

use Framework\GraphQL\ObjectType;

class StubObjectType extends ObjectType
{
    public function description(): string
    {
        return 'There is a stub description for an object type.';
    }

    public function fields(): iterable
    {
        return [
            'id' => $this->types->id(),
            // 'name' => $this->types->string(),
            'floatNumber' => $this->types->float()
        ];
    }

    public function getIdField($src): string
    {
        return md5('321');
    }

    public function getNameField($src): string
    {
        return 'static-name';
    }
    
    public function getFloatNumberField($src): float
    {
        return 321.99;
    }

    public function implements(): array
    {
        return [
            $this->types->type('StubInterface')       
        ];
    }
}
