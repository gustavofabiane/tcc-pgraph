<?php

namespace Pgraph\Core;

use Psr\Container\ContainerInterface;

class Configuration implements ContainerInterface
{
    /**
     * The configuration identifier prefix.
     *
     * @var string
     */
    protected $configurationPrefix;

    /**
     * The configuration files folder.
     *
     * @var string
     */
    protected $configurationsFolder = '';

    /**
     * The loaded configurations.
     *
     * @var array
     */
    protected $configurations = [];

    /**
     * The core application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * Create a new configurations instance from given options.
     *
     * @param array $options
     * @return static
     */
    public static function create(array $options = [])
    {
        $conf = new static();

        if ($options) {
            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'prefix':
                        $conf->setConfigurationPrefix($value);
                        break;
                    case 'folder':
                        $conf->setConfigurationsFolder($value);
                        break;
                    case 'configurations':
                        $conf->setConfigurations($value);
                        break;
                    case 'app':
                        $conf->setApplication($value);
                        break;
                }
            }
        }

        return $conf;
    }

    /**
     * Check whether the given entry is a configuration.
     *
     * @param string $entry
     * @return bool
     */
    public function isConfiguration(string $entry): bool
    {
        $pattern = sprintf('/%s.[a-zA-Z0-9\-\_]+$/', $this->configurationPrefix);
        return (bool) preg_match($pattern, $entry, $matched);
    }

    /**
     * Set new configuration if none exists.
     *
     * @param string $conf
     * @param mixed $value
     * @return static
     */
    public function set(string $conf, $value): self
    {
        if (!$this->has($conf)) {
            $this->configurations[$conf] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @param string $conf
     * @return bool
     */
    public function has($conf): bool
    {
        return isset($this->configurations[$conf]) || $this->checkConfFileExists($conf);
    }

    /**
     * Check whether the configuration is loaded from its file or not.
     *
     * @param string $conf
     * @return bool
     */
    public function loaded(string $conf): bool
    {
        return isset($this->configurations[$conf]) && $this->checkConfFileExists($conf);
    }

    /**
     * @inheritDoc
     *
     * @param string $conf
     * @param string $key
     * @return array
     */
    public function get($conf, string $key = null)
    {
        $conf = $this->filterPrefix($conf);
        if (!$this->has($conf) || !$this->loaded($conf)) {
            $this->loadConfigurationFile($conf);
        }
        if ($key) {
            return $this->configurations[$conf][$key] ?? null;
        }
        return $this->configurations[$conf] ?? null;
    }

    /**
     * Remove configuration prefix from given config entry.
     *
     * @param string $conf
     * @return string
     */
    protected function filterPrefix(string $conf): string 
    {
        if ($this->isConfiguration($conf)) {
            $conf = str_replace(
                sprintf('%s.', $this->configurationPrefix), '', $conf
            );
        }
        return $conf;
    }

    /**
     * Check whether a configuration file with the given name exists.
     *
     * @param string $conf
     * @return bool
     */
    protected function checkConfFileExists(string $conf): bool
    {
        return file_exists(sprintf('%s/%s.php', $this->getConfigurationsFolder(), $conf));
    }

    /**
     * Load a single configuration file.
     *
     * @param string $conf
     * @return void
     */
    protected function loadConfigurationFile(string $conf)
    {
        if ($this->checkConfFileExists($conf)) {
            $file = sprintf('%s/%s.php', $this->getConfigurationsFolder(), $conf);
            $this->configurations[$conf] = require $file;
        }
    }

    /**
     * Update the data from a given configuration identifier if it exists.
     *
     * @param string $conf
     * @param array $entries
     * @return void
     */
    public function update(string $conf, array $entries)
    {
        if ($this->has($conf)) {
            if (!$this->loaded($conf)) {
                $this->loadConfigurationFile($conf);
            }
            $this->configurations[$conf] = array_merge(
                $this->configurations[$conf], $entries
            );
        }
    }

    /**
     * Get the configuration identifier prefix.
     *
     * @return string
     */
    public function getConfigurationPrefix()
    {
        return $this->configurationPrefix;
    }

    /**
     * Set the configuration identifier prefix.
     *
     * @param string $configurationPrefix The configuration identifier prefix.
     *
     * @return static
     */
    public function setConfigurationPrefix(string $configurationPrefix)
    {
        $this->configurationPrefix = $configurationPrefix;
        return $this;
    }

    /**
     * Get the configuration files folder.
     *
     * @return string
     */
    public function getConfigurationsFolder()
    {
        return $this->configurationsFolder;
    }

    /**
     * Set the configuration files folder.
     *
     * @param string $configurationsFolder The configuration files folder.
     *
     * @return static
     */
    public function setConfigurationsFolder(string $configurationsFolder)
    {
        $this->configurationsFolder = $configurationsFolder;
        return $this;
    }

    /**
     * Get the loaded configurations.
     *
     * @return array
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * Set the loaded configurations.
     *
     * @param array $configurations The loaded configurations.
     *
     * @return static
     */
    public function setConfigurations(array $configurations)
    {
        $this->configurations = $configurations;
        return $this;
    }

    /**
     * Get the core application instance
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * Set the core application instance
     *
     * @param Application $app The core application instance
     * @return static
     */
    public function setApplication(Application $app)
    {
        $this->app = $app;
        return $this;
    }
}
