<?php

namespace Framework\Container;

use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use ReflectionFunction;
use ReflectionParameter;
use Psr\Container\ContainerInterface;

class ServiceResolver implements ServiceResolverInterface
{
    /**
     * Container instance
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Resolves a class, method or Closure
     *
     * @param mixed $resolvable
     * @param ContainerInterface $container
     * @param bool $lazy
     * @return mixed
     */
    public function resolve($resolvable, bool $lazy = false)
    {
        $reflected = null;

        if (is_string($resolvable) && preg_match(static::RESOLVABLE_PATTERN, $resolvable, $matches)) {
            $resolvable = [$matches[1], $matches[2]];
        } elseif (is_string($resolvable)) {
            $resolvable = [$resolvable];
        }
        
        if (is_array($resolvable) && class_exists($resolvable[0])) {
            if (!isset($resolvable[1])) {
                $reflected = new ReflectionClass($resolvable[0]);
            } else {
                if (is_object($resolvable[0])) {
                    $reflected = new ReflectionMethod($resolvable, $resolvable[1]);
                } elseif ($this->has($resolvable[0])) {
                    $reflected = new ReflectionMethod($this->get($resolvable[0]), $resolvable[1]);
                } else {
                    $class = new $resolvable[0]($this);
                    $reflected = new ReflectionMethod($class, $resolvable[1]);
                }
            }
        }

        if ($resolvable instanceof \Closure) {
            $reflected = new ReflectionFunction($resolvable);
        }

        if (!$reflected) {
            throw new RuntimeException('Cannot resolve ' . $resolvable);
        }

        if ($lazy) {
            return function () use ($reflected) {
                return $this->buildReflected($reflected);
            };
        }

        return $this->buildReflected($reflected);
    }


    /**
     * Resolve a class, method or \Closure instance using reflection
     *
     * @param ReflectionClass|ReflectionMethod|ReflectionFunction $reflected
     * @param ContainerInterface
     * @return void
     */
    protected function buildReflected($reflected)
    {
        if (!($reflected instanceof ReflectionClass ||
              $reflected instanceof ReflectionMethod ||
              $reflected instanceof ReflectionFunction)
        ) {
            throw new InvalidArgumentException(
                'Invalid argument 1 in ' . __METHOD__ . ' must be instance 
                of ReflectionClass, ReflectionMethod or ReflectionFunction, '
                . get_class($reflected) . ' given',
                500
            );
        }

        if ($reflected instanceof ReflectionClass && $reflected->hasMethod('__construct')) {
            $class = $reflected;
            $reflected = $reflected->getMethod('__construct');
        } elseif ($reflected instanceof ReflectionClass) {
            return $reflected->newInstance();
        }

        $parameters = $reflected->getParameters();
        $resolvedParams = $this->resolveParameters($parameters);

        try {
            if (isset($class)) {
                $resolved = $class->newInstanceArgs($resolvedParams);
            } elseif ($reflected instanceof ReflectionMethod) {
                $resolved = $reflected->invokeArgs($object, $resolvedParams);
            } else {
                $resolved = $reflected->invokeArgs($resolvedParams);
            }
        } catch (Exception $e) {
            throw new ContainerException(
                'Cannot resolve \'' . $reflected->getName() ?: $serviceId . '\'',
                500,
                $e
            );
        }

        return $resolved;
    }

    /**
     * Resolves the parameters types of an array of ReflectionParameter
     *
     * @param array $parameters
     * @return array
     */
    protected function resolveParameters(array $parameters)
    {
        $resolvedParameters = [];
        foreach ($parameters as $parameter) {
            $resolvedParameters[] = $this->resolveMethodParameter($parameter);
        }
        if (empty($resolvedParameters) && !empty($parameters)) {
            $resolvedParameters[] = $this->container;
        }
        return $resolvedParameters;
    }

    protected function resolveMethodParameter(ReflectionParameter $parameter) 
    {
        $paramIdentifier = $parameter->getName();
        if (!$this->container->has($paramIdentifier) && $parameter->hasType()) {
            $paramIdentifier = $parameter->getType()->__toString();
        }
        
        return $this->container->get($paramIdentifier);
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
