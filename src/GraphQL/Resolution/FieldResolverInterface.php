<?php

namespace Pgraph\GraphQL\Resolution;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Define the GraphQL field resolver signature.
 */
interface FieldResolverInterface
{
    /**
     * Resolve a type field from a given source object.
     *
     * @param array|object $source
     * @param array $args
     * @param object|\Psr\Container\ContainerInterface $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolve($source, array $args = [], $context = null, ResolveInfo $info);
}