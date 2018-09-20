<?php

namespace Framework\GraphQL\Util;

interface ImplementsInterface
{
    /**
     * Must return a list of interface types that the type implements.
     *
     * @return \GraphQL\Type\Definition\InterfaceType[]
     */
    public function implements(): array;
}