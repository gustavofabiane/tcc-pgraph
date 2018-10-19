<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use GraphQL\GraphQL;
use GraphQL\Error\Debug;
use Framework\Core\Application;
use GraphQL\Error\FormattedError;
use Framework\GraphQL\Http\Server;
use Framework\Core\ProviderInterface;
use Framework\GraphQL\ErrorFormatter;
use Framework\Container\ContainerInterface;
use GraphQL\Type\Definition\Directive;
use GraphQL\Validator\DocumentValidator;
use Framework\GraphQL\Definition\Field\PadField;
use Framework\GraphQL\Http\GraphQLRequestHandler;
use Framework\GraphQL\Definition\Enum\PadDirection;
use GraphQL\Validator\Rules\AbstractValidationRule;
use Framework\GraphQL\Resolution\DefaultFieldResolver;
use Framework\Http\Handlers\ErrorHandler;

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
            'debug' => Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE,
            'json_encoding_option' => JSON_PRETTY_PRINT,
            'internal_error_message' => 'Unexpected Error', 
            'allow_query_batching' => true,
            'query' => [],
            'mutation' => [], 
            'directives' => GraphQL::getStandardDirectives(),
            'types' => [],
            'unreachable_types' => [],
            'fields' => [
                PadField::class
            ],
            'error_formatter' => '\Framework\GraphQL\Error\BasicErrorHandler::formatError',
            'errors_handler'   => '\Framework\GraphQL\Error\BasicErrorHandler::handleErrors',
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
        $config = $this->mapConfiguration($defaultConfig, $app->config->get('graphql'));

        /**
         * Set default internal error message
         */
        FormattedError::setInternalErrorMessage($config['internal_error_message']);

        /**
         * Default rules configuration
         */
        $maxQueryComplexity = $config['security']['max_complexity'];
        if ($maxQueryComplexity !== null) {
            $queryComplexity = DocumentValidator::getRule('QueryComplexity');
            $queryComplexity->setMaxQueryComplexity($maxQueryComplexity);
        }
        $maxQueryDepth = $config['security']['max_depth'];
        if ($maxQueryDepth !== null) {
            $queryDepth = DocumentValidator::getRule('QueryDepth');
            $queryDepth->setMaxQueryDepth($maxQueryDepth);
        }
        $disableIntrospection = $config['security']['disable_introspection'];
        if ($disableIntrospection === true) {
            $disableIntrospection = DocumentValidator::getRule('DisableIntrospection');
            $disableIntrospection->setEnabled(DisableIntrospection::ENABLED);
        }

        /**
         * Register custom validation rules to the schema
         */
        foreach ($config['security']['rules'] as $rule) {
            DocumentValidator::addRule(
                $rule instanceof AbstractValidationRule
                    ? $rule 
                    : $app->resolve($rule)
            );
        }

        /**
         * Set up directives
         */
        foreach ($config['directives'] as &$directive) {
            if (!$directive instanceof Directive) {
                $directive = $app->resolve($directive);
            }
        } 

        /**
         * Type registry
         */
        if (!$app->has('typeRegistry')) {
            $app->singleton(TypeRegistry::class, function (ContainerInterface $c) {
                return new TypeRegistry($c);
            });
            $app->alias('typeRegistry', TypeRegistry::class);
            $app->implemented(TypeRegistryInterface::class, TypeRegistry::class);

            $app->registerListener(TypeRegistry::class, function (TypeRegistry $registry) use ($config) {
                foreach ($config['types'] as $name => $type) {
                    $registry->addType($type, is_string($name) ? $name : '');
                }
                foreach ($config['fields'] as $defaultName => $field) {
                    $registry->addField($field, is_string($defaultName) ? $defaultName : '');
                }
            });
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

        /**
         * GraphQL query type
         */
        if (!$app->has('graphqlQuery')) {
            $app->singleton('graphqlQuery', function (ContainerInterface $c) use ($config) {
                return $config['query'] instanceof QueryType 
                    ? $config['query']
                    : QueryType::createFromFields($config['query'], $c->get('typeRegistry'));
            });
        }

        /**
         * GraphQL mutation type
         */
        if (!$app->has('graphqlMutation')) {
            $app->singleton('graphqlMutation', function (ContainerInterface $c) use ($config) {
                return $config['mutation'] instanceof MutationType 
                    ? $config['mutation']
                    : MutationType::createFromFields($config['mutation'], $c->get('typeRegistry'));
            });
        }

        /**
         * GraphQL schema
         */
        if (!$app->has('graphqlSchema')) {
            $app->singleton('graphqlSchema', function (ContainerInterface $c) use ($config) {
                return $c->get('schemaFactory')->create(
                    $c->get('graphqlQuery'),
                    $c->get('graphqlMutation'),
                    $config['directives'],
                    $config['unreachable_types'],
                    $c->get('typeRegistry')
                );
            });
        }

        /**
         * GraphQL server
         */
        if (!$app->has('graphqlServer')) {
            $app->register('graphqlServer', function (ContainerInterface $c) use ($config) {
                
                $errorFormatter = !is_callable($config['error_formatter']) 
                    ? $c->resolve($config['error_formatter'])
                    : $config['error_formatter'];
                
                $errorsHandler = !is_callable($config['errors_handler']) 
                    ? $c->resolve($config['errors_handler'])
                    : $config['errors_handler'];

                $fieldResolder = $c->get('graphqlDefaultFieldResolver');
                if (!is_callable($fieldResolder)) {
                    $fieldResolder = [$fieldResolder, 'resolve'];
                }
                
                return new Server([
                    'debug'   => $config['debug'],
                    'schema'  => $c->get('graphqlSchema'),
                    'context' => $c,
                    'fieldResolver'  => $fieldResolder,
                    'queryBatching'  => $config['allow_query_batching'],
                    'errorsHandler'  => $errorsHandler,
                    'errorFormatter' => $errorFormatter
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
                    $config['debug'],
                    $config['json_encoding_option']
                );
                $handler->middleware($config['http']['middleware']);
                if (!empty($config['http']['headers'])) {
                    $handler->add(new AppendHeadersMiddleware($config['http']['headers']));
                }
                return $handler;
            });
        }

        if ($config['http']) {
            $app->router->collect(function($router) use ($config, $app) {
                $router->addRoute(
                    $config['http']['methods'],
                    $config['http']['endpoint'], 
                    $app->get('graphqlRequestHandler')
                );
            });
        }
    }
}
