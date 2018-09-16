<?php

declare(strict_types=1);

namespace Framework\GraphQL\Definition\Enum;

use Framework\GraphQL\Enum;

/**
 * Determines the direction of a PAD string field
 */
class PadDirection extends Enum
{
    public const PAD_BOTH = STR_PAD_BOTH;
    public const PAD_LEFT = STR_PAD_LEFT;
    public const PAD_RIGHT = STR_PAD_RIGHT;

    /**
     * The Pad direction enum type description
     *
     * @return string
     */
    public function description(): string
    {
        return 'Pad Direction determines the string position ' . 
               'for PAD concatenation in strings used by the PadField';
    }

    /**
     * Allowed field values for pad direction enum
     *
     * @return array
     */
    public function values(): array
    {
        return [
            'PAD_BOTH'  => static::PAD_BOTH,
            'PAD_LEFT'  => static::PAD_LEFT,
            'PAD_RIGHT' => static::PAD_RIGHT
        ];
    }
}
