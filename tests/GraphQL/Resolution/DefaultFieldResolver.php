<?php

namespace Framework\GraphQL\Resolution;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * The framework's default field resolver.
 */
class DefaultFieldResolver implements FieldResolverInterface
{
    /**
     * Resolves a GraphQL field using default directives.
     *
     * @param array|object $source
     * @param array $args
     * @param ContainerInterface $context
     * @param ResolveInfo $info
     * @return void
     */
    public function resolve(
        $source,
        array $args,
        ContainerInterface $context,
        ResolveInfo $info
    ) {
        $fieldName = $info->fieldName;
        $property = null;

        if (is_array($source) || $source instanceof \ArrayAccess) {
            if (isset($source[$fieldName])) {
                $property = $source[$fieldName];
            }
        } elseif (is_object($source)) {
            if (isset($source->{$fieldName})) {
                $property = $source->{$fieldName};
            }
        }

        return $property instanceof \Closure || is_callable($property) 
            ? $property($source, $args, $context) 
            : $property;
    }
}
