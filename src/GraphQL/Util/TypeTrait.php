<?php

declare(strict_types=1);

namespace Framework\GraphQL\Util;

/**
 * Type implementations common behaviors.
 */
trait TypeTrait
{
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
     * @return static
     */
    public static function setInstance(self $instance): self
    {
        static::$instance = $instance;
    }
}
