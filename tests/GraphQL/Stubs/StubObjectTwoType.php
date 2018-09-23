<?php

declare(strict_types=1);

namespace Framework\Tests\GraphQL\Stubs;

use Framework\GraphQL\ObjectType;

class StubObjectTwoType extends ObjectType
{
    public function description(): string
    {
        return 'There is a stub description for an object type.';
    }

    public function fields(): array
    {
        return [
            'id' => $this->registry->id(),
            // 'name' => $this->types->string(),
            'floatNumber' => $this->registry->float()
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
            $this->registry->type('StubInterface')       
        ];
    }
}
