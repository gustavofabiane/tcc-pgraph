<?php

namespace Framework\Container;

use Closure;
use Countable;
use Exception;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Framework\Container\Exception\ContainerException;
use Framework\Container\Exception\EntryNotFoundException;
use Framework\Container\Exception\AliasTargetNotFoundException;

/**
 * Implementation of PSR's ContainerInterface for dependency injection
 */
class Container implements
    ContainerInterface,
    IteratorAggregate,
    Countable,
    ArrayAccess
{
    /**
     * PHP types for service retrieving
     */
    const PHP_INTERNAL_TYPES = ['integer', 'int', 'string', 'double', 'float', 'null', 'array'];
    
    /**
     * Instance of a resolver that MUST implement the ServiceResolverInterface
     *
     * @var ServiceResolverInterface
     */
    private $resolver;

    /**
     * Available services registered in the container
     *
     * @var array
     */
    private $services = [];

    /**
     * Already instancied dependencies
     *
     * @var array
     */
    private $instances = [];

    /**
     * Registered aliases for services in the container
     *
     * @var array
     */
    private $aliases = [];

    /**
     * Service resolving listener callbacks
     *
     * @var array
     */
    private $listeners = [];

    /**
     * Container singleton instance
     *
     * @var static
     */
    protected static $instance;

    /**
     * Contructs the container with an array of services and a resolver
     *
     * @param array $values
     * @param ServiceResolverInterface $resolver
     */
    public function __construct(array $services = [], ServiceResolverInterface $resolver = null)
    {
        foreach($services as $id => $assembler) {
            $this->register($id, $assembler);
        }
        $this->selfProvide($resolver);
    }

    /**
     * Self provides $this and resolver instance
     *
     * @param ServiceResolverInterface $resolver
     * @return void
     */
    private function selfProvide(?ServiceResolverInterface $resolver)
    {
        $this->resolver = $resolver ?: new ServiceResolver();
        $this->resolver->setContainer($this);
        
        $this->implemented(ServiceResolverInterface::class, $this->resolver);
        $this->alias(ServiceResolver::class, ServiceResolverInterface::class);

        $this->register(static::class, $this);
        $this->alias(ContainerInterface::class, static::class);
        $this->alias(\Psr\Container\ContainerInterface::class, static::class);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new EntryNotFoundException(
                sprintf('Entry \'%s\' not found in Dependency Injection Container', $id)
            );
        }
        
        return $this->build(
            isset($this->aliases[$id]) 
            ? $this->services[$this->aliases[$id]] 
            : $this->services[$id]
        );
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id): bool
    {
        return isset($this->services[$id]) ||
               isset($this->instances[$id]) ||
               isset($this->aliases[$id]);
    }

    /**
     * Resolve the requested service
     *
     * @param array $service
     * @return mixed
     */
    protected function build(array $service)
    {
        $instance = null;

        if (isset($this->instances[$service['id']])) {
            return $this->instances[$service['id']];
        } elseif (
            in_array($service['type'], static::PHP_INTERNAL_TYPES) && !$service['implemented'] || 
            is_object($service['assembler']) && !($service['assembler'] instanceof Closure)
        ) {
            $instance = $service['assembler'];
        }
        
        if (!$service['assembler'] instanceof \Closure) {
            if ($instance === null && $this->resolver) {
                $instance = $this->buildAsResolvable($service);
            }
            
            if (!$instance) {
                throw new ContainerException(sprintf('Cannot resolve service \'%s\'', $service['id']));
            }
        } else {
            $instance = call_user_func($service['assembler'], $this);
        }
        if ($service['singleton']) {
            $this->instances[$service['id']] = $instance;
        }

        $this->callListeners($instance, $service['id']);

        return $instance;
    }

    /**
     * Resolves the service as a resolvable entry
     *
     * @param array $service
     * @return mixed
     */
    protected function buildAsResolvable(array $service)
    {
        try {
            return $this->resolver->resolve(
                $this->serviceToResolvable($service), $service['defaults']
            );
        } catch (Exception $e) {
            throw new ContainerException(
                sprintf('Cannot resolve service \'%s\'', $service['id']),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Converts a service to resolvable
     *
     * @param array $service
     * @return mixed
     */
    protected function serviceToResolvable(array $service)
    {
        $resolvable = null;

        if (class_exists($service['id']) && $service['class'] && !$service['assembler']) {
            $resolvable = $service['id'];
        } elseif ((interface_exists($service['id']) && $service['assembler']) ||
            ($service['assembler'] && $service['callable'])
        ) {
            $resolvable = $service['assembler'];
        }

        if (!$resolvable) {
            throw new ContainerException(
                sprintf('\'%s\' cannot be converted to resolvable.', $service['id'])
            );
        }

        return $resolvable;
    }
    
    /**
     * Wrap resolving in a closure instance to
     *
     * @param string $id
     * @return Closure
     */
    public function wrap($id)
    {
        if ($this->has($id)) {
            return function () use ($id) {
                return $this->get($id);
            };
        }

        throw new EntryNotFoundException(
            sprintf('Entry \'%s\' not found in Dependency Injection Container', $id)
        );
    }

    /**
     * Resolve a given callback, class, or method
     *
     * @param callable|string|array $resolvable
     * @param array $parameters
     * @param bool $wrap
     * @return mixed
     */
    public function resolve($resolvable, array $parameters = [], bool $wrap = false)
    {
        if ($wrap) {
            return function () use ($resolvable, $parameters) {
                $this->resolve($resolvable, $parameters);
            };
        }

        if (is_string($resolvable) && $this->has($resolvable)) {
            return $this->get($resolvable);
        }

        return $this->resolver->resolve($resolvable, $parameters);
        // try {
        // } catch (Exception $e) {
        //     throw new ContainerException(
        //         sprintf('Cannot resolve \'%s\'', ($resolvable instanceof Closure) ? 'closure' : $resolvable),
        //         $e->getCode(),
        //         $e
        //     );
        // }
    }

    /**
     * Adds a new service to container or overrides an existing one
     *
     * @param string $id
     * @param mixed $assembler
     * @param boolean $singleton
     * @return void
     */
    public function register(string $id, $assembler = null, $singleton = false, array $defaults = [])
    {
        if (isset($this->services[$id])) {
            unset($this->services[$id]);
        }
        if (isset($this->instances[$id])) {
            unset($this->instances[$id]);
        }

        [$assembler, $type, $isInternalType] = $this->filterAssembler($assembler);

        $service = [
            'id' => $id,
            'assembler' => $assembler,
            'singleton' => $isInternalType || $singleton,
            'class' => class_exists($id),
            'implemented' => interface_exists($id) && 
                             (is_callable($assembler) || 
                             ($assembler !== null && static::implements($assembler, $id))),
            'callable' => !$isInternalType && (is_callable($assembler) || $assembler instanceof Closure),
            'type' => $type,
            'defaults' => $defaults
        ];

        $this->services[$id] = $service;
        $this->listeners[$id] = [];
    }

    /**
     * Filter the provided service assembler
     * 
     * @see register()
     *
     * @param mixed $assembler
     * @return mixed
     */
    protected function filterAssembler($assembler)
    {
        if (is_bool($assembler)) {
            $value = $assembler;
            $assembler = function () use ($value) {
                return $value;
            };
        }
        
        $type = $assembler !== null ? gettype($assembler) : null;
        $isInternalType = in_array($type, static::PHP_INTERNAL_TYPES);

        return [$assembler, $type, $isInternalType];
    }

    /**
     * Adds a service as singleton
     *
     * Singleton services are initialized only once,
     * then the same instance is retrieved every type
     * its entry is called.
     *
     * @param string $id
     * @param mixed $assembler
     * @param array $defaults
     * @return void
     */
    public function singleton(string $id, $assembler = null, array $defaults = [])
    {
        $this->register($id, $assembler, true, $defaults);
    }

    /**
     * Adds a service as the implementation of a Interface
     *
     * @param string $interface
     * @param mixed $implemented
     * @param boolean $singleton
     * @return void
     */
    public function implemented(string $interface, $implemented = null, $singleton = false)
    {
        if (!static::implements($implemented, $interface)) {
            throw new ContainerException(
                sprintf('\'%s\' must implements \'%s\'')
                (is_string($implemented) ? $implemented : get_class($implemented)),
                $interface
            );
        }
        $this->register($interface, $implemented, $singleton);
    }

    /**
     * Register a service with default resolvable parameters
     *
     * @param string $id
     * @param mixed $assembler
     * @param array $defaults
     * @return void
     */
    public function registerWithDefaults($id, $assembler, array $defaults)
    {
        $this->register($id, $assembler, false, $defaults);
    }

    /**
     * Register a service name alias
     *
     * @param string $alias
     * @param string $target
     * @return void
     */
    public function alias(string $alias, string $target)
    {
        if (!$this->has($target)) {
            throw new AliasTargetNotFoundException(
                sprintf('\'%s\' is not registered in the container', $target)
            );
        }
        $this->aliases[$alias] = $target;
    }

    /**
     * Call service resolved registered listeners
     *
     * @param mixed $instance
     * @param string $id
     * @return void
     */
    protected function callListeners($instance, string $id)
    {
        if (!empty($this->listeners[$id])) {
            foreach ($this->listeners[$id] as $listener) {
                $listener($instance, $this);
            }
        }
    }

    /**
     * Register a listener to be executed after the service is resolved
     *
     * @param string $id
     * @param callable $callback
     * @return void
     */
    public function registerListener(string $id, callable $callback)
    {
        if ($this->has($id)) {
            if (in_array($callback, $this->listeners[$id])) {
                throw new ContainerException(
                    'Duplicated service resolving listener'
                );
            }
            $this->listeners[$id][] = $callback;
        }
    }

    /**
     * Binds a service resolver to the container
     *
     * @param ServiceResolverInterface $resolver
     * @return void
     */
    public function setResolver(ServiceResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Returns the service resolver of the container
     *
     * @return ServiceResolverInterface
     */
    public function getResolver(): ServiceResolverInterface
    {
        return $this->resolver;
    }

    /**
     * Checks if a class implements de given interface
     *
     * @param mixed $class
     * @param string $interface
     * @return boolean
     */
    public static function implements($class, string $interface): bool
    {
        if (is_string($class) && !class_exists($class)) {
            throw new ContainerException(sprintf('Class \'%s\' does not exists', $class));
        }
        return array_key_exists($interface, class_implements($class));
    }
    
    /**
     * Get the container singleton instance or creates one if none exists.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Set the singleton container instance
     *
     * @param static $instance
     * @return void
     */
    public static function setInstance(Container $instance)
    {
        static::$instance = $instance;
    }

    /*
    *   Implements ArrayAccess, IteratorAggregate, Countable
    */

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($id)
    {
        return $this->get($id);
    }

    public function offsetSet($id, $assembler)
    {
        $this->register($id, $assembler);
    }

    public function offsetUnset($id)
    {
        //
    }

    public function getIterator()
    {
        return new ArrayIterator($this->services);
    }

    public function count()
    {
        return count($this->services);
    }

    /**
     * Magic Methods
     */

    public function __get($id)
    {
        return $this->get($id);
    }

    public function __set($id, $assembler)
    {
        $this->register($id, $assembler);
    }

    public function __isset($id)
    {
        return $this->has($id);
    }
}
