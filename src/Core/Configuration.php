<?php

namespace Framework\Core;

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
     * The core application instance
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
        $pattern = sprintf('/%s.[a-zA-Z0-9\-\_]+/', $this->configurationPrefix);
        return (bool) preg_match($pattern, $entry, $matched);
    }

    /**
     * @inheritDoc
     *
     * @param string $conf
     * @return bool
     */
    public function has($conf)
    {
        if (isset($this->configurations[$conf])) {
            return true;
        }
        return $this->checkConfFileExists($conf);
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
        if (!$this->has($conf)) {
            $this->loadConfigurationFile($conf);
        }
        if ($key) {
            return $this->configurations[$conf][$key] ?? null;
        }
        return $this->configurations[$conf] ?? null;
    }

    /**
     * Check whether a configuration file with the given name exists.
     *
     * @param string $conf
     * @return bool
     */
    protected function checkConfFileExists(string $conf): bool
    {
        return !empty(glob(sprintf('%s/%s.php', $this->getConfigurationsFolder(), $conf)));
    }

    /**
     * Load a single configuration file.
     *
     * @param string $conf
     * @return void
     */
    protected function loadConfigurationFile(string $conf)
    {
        $files = glob(sprintf('%s/%s.php', $this->getConfigurationsFolder(), $conf));
        $confFile = $files[0];
        $this->configurations[$conf] = require $confFile;
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
