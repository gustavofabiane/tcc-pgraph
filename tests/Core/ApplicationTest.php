<?php

namespace Framework\Tests\Core;

use Framework\Core\Application;
use PHPUnit\Framework\TestCase;
use Framework\Router\RouterInterface;
use Framework\Http\Handlers\NotFoundHandler;
use Framework\Http\Handlers\ErrorHandlerInterface;

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
        $this->application = new Application(['settings' => ['router' => []]]);
    }

    public function testCreateApplicationInstance()
    {
        $this->assertInstanceOf(Application::class, $this->application);
        $this->assertInstanceOf(RouterInterface::class, $this->application->router);
        $this->assertInstanceOf(NotFoundHandler::class, $this->application->notFoundHandler);
        $this->assertInstanceOf(ErrorHandlerInterface::class, $this->application->errorHandler);
    }
}
