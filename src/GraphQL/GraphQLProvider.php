<?php

namespace Framework\GraphQL;

use Framework\Core\Application;
use Psr\Container\ContainerInterface;
use Framework\GraphQL\Resolution\DefaultFieldResolver;
use Framework\GraphQL\Http\GraphQLRequestHandler;
use Framework\Core\ProviderInterface;
use Framework\GraphQL\Definition\Enum\PadDirection;
use Framework\GraphQL\Definition\Field\PadField;

class GraphQLProvider implements ProviderInterface
{
    protected function mapConfiguration(array $default, array $configuration): array
    {
        $mappedConfiguration = [];

        foreach ($default as $key => $value) {
            if (!isset($configuration[$key])) {
                $mappedConfiguration[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                $mappedConfiguration[$key] = $this->mapConfiguration($default[$key], $configuration[$key]);
            } else {
                $mappedConfiguration[$key] = $configuration[$key];
            }
        }

        return $mappedConfiguration;
    }

    public function provide(Application $app)
    {
        $defaultConfig = [
            'debug' => false,
            'allow_query_batching' => true,
            'query' => [],
            'mutation' => [],
            'types' => [
                PadDirection::class
            ],
            'fields' => [
                PadField::class
            ],
            'security' => [
                'max_complexity' => null,
                'max_depth' => null,
                'disable_introspection' => false
            ],
            'http' => [
                'path' => '/graphql',
                'methods' => ['GET', 'POST'],
                'headers' => [],
                'middleware' => [],

            ]
        ];
        
        $config = $this->mapConfiguration($defaultConfig, $app->get('config.graphql'));

        /**
         * Type registry
         */
        if (!$app->has('typeRegistry')) {
            $app->singleton(TypeRegistry::class, function (ContainerInterface $c) {
                return new SchemaFactory($c->get('typeRegistry'));
            });
            $app->alias('typeRegistry', TypeRegistry::class);
            $app->implemented(TypeRegistryInterface::class, TypeRegistry::class);
        }

        /**
         * Schema factory
         */
        if (!$app->has('schemaFactory')) {
            $app->singleton('schemaFactory', function (ContainerInterface $c) {
                return new SchemaFactory($c->get('typeRegistry'));
            });
            $app->alias(SchemaFactory::class, 'schemaFactory');
        }

        /**
         * Schema default field resolver
         */
        if (!$app->has('defaultFieldResolver')) {
            $app->singleton('defaultFieldResolver', function (ContainerInterface $c) {
                return new DefaultFieldResolver();
            });
        }

        if (!$app->has('graphqlRequestHandler')) {
            $app->register('graphqlRequestHandler', function (ContainerInterface $c) use ($config) {
                return new GraphQLRequestHandler(
                    $c->get('graphqlServer'), 
                    $config['debug']
                );
            });
        }
    }
}
