<?php

namespace Framework\GraphQL\Util;

interface TypeWithFields
{
    /**
     * Return an array defining the type fields.
     *
     * @return array
     */
    public function fields(): array;
    
    /**
     * Must try to infer the field resolver method if exists.
     *
     * @param string $fieldName
     * @return callable|null
     */
    public function getFieldResolver(string $fieldName): ?callable;
}