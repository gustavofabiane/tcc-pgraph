<?php

namespace Framework\Tests\Core;

use PHPUnit\Framework\TestCase;
use Framework\Core\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * Configuration instance
     *
     * @var Configuration
     */
    protected $config;

    /**
     * Test data config
     *
     * @var array
     */
    protected $testingData = [
        'testing' => [
            'php' => '7',
            'java' => '8',
            'python' => '2'
        ]
    ];

    public function setup()
    {
        $this->config = new Configuration();
        $this->config->setConfigurationPrefix('config');
        $this->config->setConfigurationsFolder(__DIR__ . '/../utils/config');
        $this->config->setConfigurations($this->testingData);
    }

    public function testIsConfiguration()
    {
        $this->assertTrue($this->config->isConfiguration('config.dummy'));
        $this->assertFalse($this->config->isConfiguration('config.not&/exist'));
    }

    public function testGetNonExistentConfiguration()
    {
        $this->assertInternalType('null', $this->config->get('not-existent'));
    }

    public function testGetLoadedConfiguration()
    {
        $testingConfig = $this->config->get('testing');
        $this->assertEquals($this->testingData['testing'], $testingConfig);
    }

    public function testGetAndLoadFromFile()
    {
        $fromFileConf = require $this->config->getConfigurationsFolder() . '/from-file.php';        
        $testingFileConfig = $this->config->get('from-file');
        
        $this->assertEquals($fromFileConf, $testingFileConfig);
    }
}
