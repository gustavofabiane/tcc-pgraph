<?php

namespace Pgraph\Container;

use Error;
use Closure;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use ReflectionFunction;
use ReflectionParameter;
use Psr\Container\ContainerInterface;
use Pgraph\Container\Exception\ContainerException;

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
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function resolve($resolvable, array $parameters = [])
    {
        if ($resolvable instanceof Closure || (is_string($resolvable) && function_exists($resolvable))) {
            return $this->resolveFunctionCall($resolvable, $parameters);
        } elseif (is_array($resolvable) || is_string($resolvable) || class_exists($resolvable)) {
            return $this->resolveClassCall($resolvable, $parameters);
        }

        if (is_array($resolvable)) {
            $resolvable = implode('::', $resolvable);
        }
        throw new RuntimeException('Cannot resolve \'' . $resolvable . '\'');
    }

    /**
     * Resolve a function or Closure
     *
     * @param string|CLosure $resolvable
     * @param array $parameters
     * @return mixed
     */
    protected function resolveFunctionCall($resolvable, array $parameters = [])
    {
        return $this->buildReflected(new ReflectionFunction($resolvable), $parameters);
    }

    /**
     * Resolve a class instance or class method call
     *
     * @param string|array $resolvable
     * @param array $parameters
     * @return mixed
     */
    protected function resolveClassCall($resolvable, array $parameters = [])
    {
        if (is_string($resolvable) && preg_match(static::RESOLVABLE_PATTERN, $resolvable, $matches)) {
            $resolvable = [$matches[1], $matches[2]];
        } elseif (is_string($resolvable)) {
            $resolvable = [$resolvable];
        }
        
        if (count($resolvable) > 1) {
            return $this->buildReflected(
                new ReflectionMethod($resolvable[0], $resolvable[1]),
                $parameters,
                $resolvable[0]
            );
        }

        return $this->buildReflected(new ReflectionClass($resolvable[0]), $parameters);
    }


    /**
     * Resolve a class, method or Closure instance using reflection
     *
     * @param ReflectionClass|ReflectionMethod|ReflectionFunction $reflected
     * @param array $parameters Default parameters to bind to the called reflection
     * @param object $declaringClass for reflection methods, the method declaring class instance or name
     * @return void
     */
    protected function buildReflected($reflected, array $parameters = [], $declaringClass = null)
    {
        if (!($reflected instanceof ReflectionClass ||
              $reflected instanceof ReflectionMethod ||
              $reflected instanceof ReflectionFunction)
        ) {
            throw new \InvalidArgumentException(
                'Invalid argument 1 in ' . __METHOD__ . ' must be instance 
                of ReflectionClass, ReflectionMethod or ReflectionFunction, '
                . get_class($reflected) . ' given',
                500
            );
        }

        if ($reflected instanceof ReflectionClass && $reflected->hasMethod('__construct')) {
            $class = $reflected;
            $reflected = $reflected->getConstructor();
        } elseif ($reflected instanceof ReflectionClass) {
            return $reflected->newInstance();
        }

        try {
            $reflectedParameters = $reflected->getParameters();
            $resolvedParams = $this->resolveParameters($reflectedParameters, $parameters);

            if (isset($class)) {
                $resolved = $class->newInstanceArgs($resolvedParams);
            } elseif ($reflected instanceof ReflectionMethod) {
                $declaringClassObject = is_object($declaringClass)
                    ? $declaringClass
                    : $this->buildReflected(new ReflectionClass($declaringClass), $parameters);
                $resolved = $reflected->invokeArgs($declaringClassObject, $resolvedParams);
            } elseif ($reflected instanceof ReflectionFunction) {
                $resolved = $reflected->invokeArgs($resolvedParams);
            }
        } catch (Exception $e) {
            throw new ContainerException(
                sprintf('Cannot resolve \'%s\' due to exception: \'%s\'', $reflected->getName(), $e->getMessage()),
                500,
                $e
            );
        } catch (Error $e) {
            throw new ContainerException(
                sprintf('Cannot resolve \'%s\' due to error: \'%s\'', $reflected->getName(), $e->getMessage()),
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
                $resolvedParameter = $this->resolveParameterWithContainer($parameter) ?: $this->resolveParameter($parameter);
                if ($resolvedParameter) {
                    $resolvedParameters[] = $resolvedParameter;
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $resolvedParameters[] = $parameter->getDefaultValue();
                }
            }
        }
        if (empty($resolvedParameters) && !empty($parameters)) {
            $resolvedParameters[] = $this->container;
        }
        return $resolvedParameters;
    }

    /**
     * Resolve a parameter not expected to be found in the container
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     */
    protected function resolveParameter(ReflectionParameter $parameter)
    {
        if ($parameter->hasType() && class_exists($type = $parameter->getType())) {
            return $this->resolve($type->__toString());
        }
        return null;
    }

    /**
     * Resolve reflection parameter using the dependency container
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     *
     * @throws ContainerException
     */
    protected function resolveParameterWithContainer(ReflectionParameter $parameter)
    {
        $paramIdentifier = $parameter->getName();
        if (!$this->container->has($paramIdentifier) && $parameter->hasType()) {
            $paramIdentifier = $parameter->getType()->__toString();
        }
        if ($this->container->has($paramIdentifier)) {
            return $this->container->get($paramIdentifier);
        }
        return null;
    }

    /**
     * Set the resolver container implementation
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get the resolver container implementation
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
