<?php

namespace Pgraph\Tests\Core;

use Pgraph\Core\Application;
use PHPUnit\Framework\TestCase;
use Pgraph\Router\RouterInterface;
use Pgraph\Http\Handlers\NotFoundHandler;
use Pgraph\Http\Handlers\ErrorHandlerInterface;
use Pgraph\Core\Configuration;

class ApplicationTest extends TestCase
{
    /**
     * Application instance
     *
     * @var Application
     */
    protected $application;

    public function setup()
    {
        $this->application = new Application();
    }

    public function testCreateApplicationInstance()
    {
        $this->assertInstanceOf(Application::class, $this->application);
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf(Configuration::class, $this->application->config);
        $this->assertInstanceOf(Configuration::class, $this->application->get('config'));
        $this->assertSame($this->application->config, $this->application->get('config'));
    }
}
