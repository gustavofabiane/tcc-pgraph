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

    public function testCreateStatic()
    {
        $this->assertEquals($this->config, Configuration::create([
            'prefix' => 'config',
            'folder' => __DIR__ . '/../utils/config',
            'configurations' => $this->testingData
        ]));
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
        
        $this->assertTrue($this->config->has('from-file'));
        $this->assertFalse($this->config->loaded('from-file'));

        $testingFileConfig = $this->config->get('from-file');
        
        $this->assertTrue($this->config->loaded('from-file'));
        $this->assertEquals($fromFileConf, $testingFileConfig);
    }

    public function testUpdateLoadedConfiguration()
    {
        $this->config->update('testing', ['php' => '7.1']);
        $this->assertEquals(['php' => '7.1'] + $this->testingData['testing'], $this->config->get('testing'));
    }
    
    public function testUpdateConfigLoadedFromFile()
    {
        $fromFileConf = require $this->config->getConfigurationsFolder() . '/from-file.php';        
        $this->config->update('from-file', ['debug' => false, 'test' => true]);
        
        $this->assertEquals(['debug' => false, 'test' => true] + $fromFileConf, $this->config->get('from-file'));
    }

    public function testSetConfiguration()
    {
        $this->config->set('set-test', $conf = ['confi' => 'guration']);
        $this->assertEquals($conf, $this->config->get('set-test'));
    }
}
