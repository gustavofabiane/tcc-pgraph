<?php

declare(strict_types=1);

namespace Framework\GraphQL\Util;

use Framework\GraphQL\TypeRegistryInterface;
use GraphQL\Type\Definition\FieldDefinition;


/**
 * Type implementations common behaviors.
 */
trait TypeTrait
{
    /**
     * The type resgistry instance.
     *
     * @var TypeRegistryInterface
     */
    protected $registry;

    /**
     * The enum type name.
     *
     * @var string
     */
    public $name;

    /**
     * The enum type description.
     *
     * @var string
     */
    public $description;

    /**
     * The type global instance.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Empty constructor for dependency injection in containers. 
     * Note: You can override the constructor with registered application dependencies.
     * 
     */
    public function __construct()
    {
        ///
    }

    /**
     * Get the type name if possible.
     *
     * @return string
     */
    public function name(): string
    {
        if (!$this->name && method_exists($this, 'tryInferName')) {
            $this->name = $this->tryInferName();
        }
        return $this->name;
    }

    /**
     * Try to infer the field resolver method defined in the type implementation.
     *
     * @param string $fieldName
     * @return callable|null
     */
    public final function getFieldResolver(string $fieldName): ?callable
    {
        $fieldName = ucfirst($fieldName);
        $formats = ['get%sField', 'get%s', 'resolve%sField'];
        
        foreach ($formats as $format) {
            $fieldResolverName = sprintf($format, $fieldName);
            if (method_exists($this, $fieldResolverName)) {
                return [$this, $fieldResolverName];
            }
        }
        return null;
    }

    /**
     * Set the types registry.
     *
     * @param TypeRegistryInterface $registry
     * @return static
     */
    public function setTypeRegistry(TypeRegistryInterface $registry)
    {
        $this->registry = $registry;
        return $this;
    }

    /**
     * Get the type global instance.
     *
     * @return static
     */
    public static function getInstance(): self
    {
        return static::$instance;
    }

    /**
     * Set the type global instance.
     *
     * @param static $instance
     * @return void
     */
    public static function setInstance(self $instance)
    {
        static::$instance = $instance;
    }
}
