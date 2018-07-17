<?php

namespace Framework\Container;

use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use ReflectionFunction;
use ReflectionParameter;
use Psr\Container\ContainerInterface;
use Framework\Container\Exception\EntryNotFoundException;

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
     * @param bool $deffered
     * @return mixed
     */
    public function resolve($resolvable, bool $deffered = false, array $parameters = [])
    {
        $reflected = null;

        if (is_string($resolvable) && preg_match(static::RESOLVABLE_PATTERN, $resolvable, $matches)) {
            $resolvable = [$matches[1], $matches[2]];
        } elseif (is_string($resolvable)) {
            $resolvable = [$resolvable];
        }
        
        if (is_array($resolvable) && 
            (is_object($resolvable[0]) || 
            (is_string($resolvable[0]) && class_exists($resolvable[0])))
        ) {
            $reflected = !isset($resolvable[1]) ? 
                new ReflectionClass($resolvable[0]) : 
                new ReflectionMethod($resolvable[0], $resolvable[1]);
        }

        if ($resolvable instanceof \Closure) {
            $reflected = new ReflectionFunction($resolvable);
        }

        if (!$reflected) {
            throw new RuntimeException('Cannot resolve ' . $resolvable);
        }

        if ($deffered) {
            return function () use ($reflected, $parameters) {
                return $this->buildReflected($reflected, $parameters);
            };
        }

        return $this->buildReflected($reflected, $parameters);
    }


    /**
     * Resolve a class, method or \Closure instance using reflection
     *
     * @param ReflectionClass|ReflectionMethod|ReflectionFunction $reflected
     * @param ContainerInterface
     * @return void
     */
    protected function buildReflected($reflected, array $parameters = [])
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

        $reflectedParameters = $reflected->getParameters();
        $resolvedParams = $this->resolveParameters($reflectedParameters, $parameters);

        try {
            if (isset($class)) {
                $resolved = $class->newInstanceArgs($resolvedParams);
            } elseif ($reflected instanceof ReflectionMethod) {
                $resolvedClassObject = $this->buildReflected($reflected->getDeclaringClass(), $parameters);
                $resolved = $reflected->invokeArgs($resolvedClassObject, $resolvedParams);
            } elseif ($reflected instanceof ReflectionFunction) {
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
    protected function resolveParameters(array $reflectedParameters, array $userDefaultParameters = [])
    {
        $resolvedParameters = [];
        foreach ($reflectedParameters as $parameter) {
            $overrideName = $parameter->getName();
            if (array_key_exists($overrideName, $userDefaultParameters)) {
                $resolvedParameters[] = $userDefaultParameters[$overrideName];
            } else {
                try {
                    $resolvedParameters[] = $this->resolveParameterWithContainer($parameter);
                } catch (EntryNotFoundException $e) {
                    if (!$parameter->isDefaultValueAvailable()) {
                        throw $e;
                    }
                    $resolvedParameters[] = $parameter->getDefaultValue();
                }
            }
        }
        if (empty($resolvedParameters) && !empty($parameters)) {
            $resolvedParameters[] = $this->container;
        }
        return $resolvedParameters;
    }

    protected function resolveParameterWithContainer(ReflectionParameter $parameter) 
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
