<?php

declare(strict_types=1);

namespace Framework\GraphQL\Util;

/**
 * ArrayAccess interface implementation behaviors for types and fields
 */
trait ArrayAccessTrait
{
    /**
     * ArrayAccess implementation
     */
    public function offsetGet($offset)
    {
        return $this->{$offset} ?? null;
    }

    /**
     * ArrayAccess implementation
     */
    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            $this->{$offset} = $value;
        }
    }

    /**
     * ArrayAccess implementation
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
     * ArrayAccess implementation
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->{$offset} = null;
        }
    }
}
