<?php

namespace Framework\GraphQL;

use Framework\Core\Application;
use Framework\GraphQL\Http\Server;
use Framework\Core\ProviderInterface;
use Psr\Container\ContainerInterface;
use Framework\GraphQL\Definition\Field\PadField;
use Framework\GraphQL\Http\GraphQLRequestHandler;
use Framework\GraphQL\Definition\Enum\PadDirection;
use Framework\GraphQL\Resolution\DefaultFieldResolver;

class GraphQLProvider implements ProviderInterface
{
    protected function mapConfiguration(array $default, array $configuration): array
    {
        $goRecursive = ['security'];
        $mappedConfiguration = [];
        foreach ($default as $key => $value) {
            if (!isset($configuration[$key])) {
                ///
            } elseif (in_array($key, $goRecursive)) {
                $value = $this->mapConfiguration($value, $configuration[$key]);
            } elseif (is_array($value)) {
                $value = array_merge($value, $configuration[$key]);    
            } else {
                $value = $configuration[$key];
            }
            $mappedConfiguration[$key] = $value;
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
                'disable_introspection' => false,
                'rules' => []
            ],
            'http' => [
                'endpoint' => '/graphql',
                'methods'  => ['GET', 'POST'],
                'headers'  => [],
                'middleware' => [],
            ]
        ];
        
        $config = $this->mapConfiguration($defaultConfig, $app->get('config.graphql'));

        /**
         * Type registry
         */
        if (!$app->has('typeRegistry')) {
            $app->singleton(TypeRegistry::class, function (ContainerInterface $c) {
                return new TypeRegistry($c);
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
        if (!$app->has('graphqlDefaultFieldResolver')) {
            $app->singleton('graphqlDefaultFieldResolver', function (ContainerInterface $c) {
                return new DefaultFieldResolver();
            });
        }

        if (!$app->has('graphqlServer')) {
            $app->register('graphqlServer', function (ContainerInterface $c) use ($config) {
                return new Server([
                    'debug' => $config['debug'],
                    'schema' => $c->get('graphqlSchema'),
                    'context' => $c,
                    'fieldResolver' => $c->get('graphqlDefaultFieldResolver'),
                    'queryBatching' => $config['allow_query_batching'],
                    'validationRules' => $config['security']['rules']
                ]);
            });
        }

        /**
         * GraphQL http request handler.
         */
        if (!$app->has('graphqlRequestHandler')) {
            $app->register('graphqlRequestHandler', function (ContainerInterface $c) use ($config) {
                $handler = new GraphQLRequestHandler(
                    $c->get('graphqlServer'), 
                    $config['debug']
                );
                $handler->middleware($config['http']['middleware']);
                if (!empty($config['http']['headers'])) {
                    $handler->add(function ($request, $handler) use ($config) {
                        $response = $handler->handle($request);
                        foreach ($config['http']['headers'] as $name => $value) {
                            $response = $response->withAddedHeader($name, $value);
                        }
                        return $response;
                    });
                }
                return $handler;
            });
        }

        if ($config['http']) {
            $app->router->collect(function($router) use ($config, $app) {
                $router->addRoute(
                    $config['http']['endpoint'], 
                    $app->get('graphqlRequestHandler')
                );
            });
        }
    }
}
