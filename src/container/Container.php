<?php

namespace Framework\Container;

use Countable;
use Exception;
use Reflector;
use ArrayAccess;
use ArrayIterator;
use ReflectionClass;
use ReflectionMethod;
use IteratorAggregate;
use ReflectionFunction;
use ReflectionParameter;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Framework\Container\Exception\ContainerException;
use Framework\Container\Exception\EntryNotFoundException;

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
    const PHP_SIMPLE_TYPES = ['integer', 'int', 'string', 'double', 'float', 'null', 'array'];
    
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
     * Contructs the container with an array of services and a resolver
     *
     * @param array $values
     * @param ServiceResolverInterface $resolver
     */
    public function __construct(array $services = [], ServiceResolverInterface $resolver = null)
    {
        foreach($services as $id => $assembler) {
            $this->add($id, $assembler);
        }
        $this->resolver = $resolver ?: new ServiceResolver();
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
            throw new EntryNotFoundException($id . ' not found in Dependency Injection Container');
        }
        
        return $this->build($this->services[$id]);
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
               isset($this->instances[$id]);
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
            $instance = $this->instances[$service['id']];
        } elseif (in_array($service['type'], self::PHP_SIMPLE_TYPES) && !$service['implemented']) {
            $instance = $service['assembler'];
        } elseif (is_object($service['assembler']) && !($service['assembler'] instanceof \Closure)) {
            $instance = $service['assembler'];
        }
        
        if (!$instance && $this->resolver) {
            $instance = $this->buildAsResolvable($service);
        }
        
        if (!$instance && $service['assembler'] instanceof \Closure) {
            $instance = $service['assembler']($this);
        }
            
        if (!$instance) {
            throw new ContainerException('Cannot resolve service \'' . $service['id'] . '\'');
        }

        if ($service['singleton']) {
            $this->instances[$service['id']] = $instance;
        }

        return $instance;
    }

    protected function buildAsResolvable(array $service)
    {
        try {
            $instance = $this->resolver->resolve($this->serviceToResolvable($service), $this);
            return $instance;
        } catch (Exception $e) {
            throw new ContainerException(
                'Cannot resolve service \'' . $service['id'] . '\'',
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
            throw new ContainerException($service['id'] . ' cannot be converted to resolvable.');
        }

        return $resolvable;
    }

    /**
     * Adds a new service to container or overrides an existing one
     *
     * @param string $id
     * @param mixed $assembler
     * @param boolean $singleton
     * @return void
     */
    public function add(string $id, $assembler = null, $singleton = false)
    {
        if (isset($this->services[$id])) {
            unset($this->services[$id]);
        }
        if (isset($this->instances[$id])) {
            unset($this->instances[$id]);
        }
        
        $type = $assembler ? gettype($assembler) : null;

        $service = [
            'id' => $id,
            'assembler' => $assembler,
            'singleton' => in_array($type, self::PHP_SIMPLE_TYPES) || $singleton,
            'class' => class_exists($id) && !$assembler,
            'implemented' => (interface_exists($id) && class_exists($assembler) && 
                              self::implements($assembler, $id)) || 
                              is_callable($assembler),
            'callable' => is_callable($assembler) || $assembler instanceof \Closure,
            'type' => $type
        ];

        $this->services[$id] = $service;
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
     * @return void
     */
    public function singleton(string $id, $assembler = null)
    {
        $this->add($id, $assembler, true);
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
        if (!self::implements($implemented, $interface)) {
            throw new ContainerException(
                (is_string($implemented) ? $implemented : get_class($implemented)) .
                ' must implements ' . $interface
            );
        }
        $this->add($interface, $implemented, $singleton);
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
    public function getResolver()
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
        return array_key_exists($interface, class_implements($class));
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
        $this->add($id, $assembler);
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
        $this->add($id, $assembler);
    }

    public function __isset($id)
    {
        return $this->has($id);
    }
}