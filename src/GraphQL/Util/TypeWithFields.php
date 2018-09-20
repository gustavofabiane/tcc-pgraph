<?php

namespace Framework\GraphQL\Util;

interface TypeWithFields
{
    /**
     * Return an iterable instance defining the type fields.
     *
     * @return iterable
     */
    public function fields(): iterable;
    
    /**
     * Must try to infer the field resolver method if exists.
     *
     * @param string $fieldName
     * @return callable|null
     */
    public function getFieldResolver(string $fieldName): ?callable;
}