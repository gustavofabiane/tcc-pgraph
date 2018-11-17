<?php

declare(strict_types=1);

namespace Pgraph\GraphQL\Definition\Field;

use ArrayAccess;
use Pgraph\GraphQL\Field;
use GraphQL\Type\Definition\Type;
use Pgraph\GraphQL\Definition\Enum\PadDirection;

/**
 * Abstract implementation of custom field definitions
 */
class PadField extends Field
{
    public function description(): string
    {
        return 'This field defines a string with a minimum ' .
               'length and complete its missing characters ' . 
               'with a PAD string defined by the client'; 
    }

    public function type(): Type
    {
        return $this->registry->string();
    }

    public function args(): array
    {
        return [
            'pad' => [
                'type' => $this->registry->string(),
                'default_value' => '0'
            ], 
            'direction' => [
                'type' => $this->registry->padDirection(),
                'default_value' => PadDirection::PAD_LEFT
            ],
            'size' => $this->registry->int()
        ];
    }

    public function resolve($src, array $args = [])
    {
        $value = $src->{$this->key};
        $args += ['size' => strlen($value)];
        
        return str_pad($value, $args['size'], $args['pad']);
    }
}