<?php

namespace Framework\GraphQL\Resolution;

use GraphQL\Type\Definition\ObjectType;
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
     * @return mixed
     */
    public function resolve($source, array $args = [], $context = null, ResolveInfo $info) 
    {
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

            $type = $info->parentType;
            if ($resolveMethod = $this->getFieldResolver($type, $fieldName)) {
                $property = [$type, $resolveMethod];
            }
        }
var_dump($property);
        return $property instanceof \Closure || is_callable($property) 
            ? call_user_func($property, $source, $args, $context, $info) 
            : $property;
    }

    /**
     * Try to find the field resolver in the source type instance
     *
     * @param object|array $source
     * @param string $fieldName
     * @return string|null
     */
    protected function getFieldResolver(ObjectType $type, string $fieldName): ?string
    {
        if (strpos($fieldName, '_') !== false) {
            $aux = implode(ucwords(str_replace('_', ' ', $fieldName)));
            $fieldName = str_replace(' ', '', $aux);
        }
        
        if (method_exists($type, $fieldName)) {
            return $fieldName;
        }

        $fieldNameInResolver = ucfirst($fieldName);
        
        $formats = ['get%', 'get%sField', 'resolve%sField'];
        foreach ($formats as $methodFormat) {
            $fieldResolverName = sprintf($methodFormat, $fieldNameInResolver);
            if (method_exists($type, $fieldResolverName)) {
                return $fieldResolverName;
            }
        }

        return null;
    }
}
