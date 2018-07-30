<?php

namespace Framework;

use Framework\Http\Uri;
use Framework\Http\Request;
use Framework\Http\UploadedFile;
use Framework\Container\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Http\Handlers\RequestHandlerTrait;

class Application extends Container implements RequestHandlerInterface
{
    use RequestHandlerTrait;

    /**
     * Array of the application settings
     *
     * @var array
     */
    protected $settings;

    /**
     * Creates a new application instance
     *
     * @param array $services
     */
    public function __construct(array $services)
    {
        $this->settings = $services['settings'] ?: [];

        parent::__construct($services);

        $this->registerDefaultServices();
        $this->bootServices();
    }

    /**
     * Executes the application
     *
     * @return void
     */
    public function execute(?ServerRequestInterface $request = null)
    {
        $response = $this->handle($request ?: $this->get('request'));
        $this->emitResponse($response);
    }

    /**
     * Handle the server request recieved and then
     * returns a response after middleware stack process.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            if ($this->hasMiddleware()) {
                $response = $this->processMiddleware($request);
            } elseif (!$response = $this($request)) {
                $response = $this->get('notFoundHandler')->handle($request);
            }
        } catch (Exception $exception) {
            if (!$this->errorHandler) {
                throw $exception;
            }
            $response = $this->errorHandler->handle($request, $exception);
        }

        return $response;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {


        return $response;
    }

    public function emitResponse(ResponseInterface $response)
    {
        //
    }

    /**
     * Register all default services of the application if those have not been defined yet.
     *
     * @return void
     */
    private function registerDefaultServices()
    {
        /**
         * Register the server request
         */
        if (!$this->has('request')) {
            $this->singleton('request', function (ContainerInterface $container) {
                return new Request(
                    $_SERVER["REQUEST_METHOD"] ?? 'GET',
                    $_SERVER ?? [],
                    Uri::createFromServerParams($_SERVER),
                    [],
                    $_COOKIES,
                    new Body('php://input', 'r'),
                    UploadedFile::filterNativeUploadedFiles($_FILES)
                );
            });
        }

        if (!$this->has('router')) {
            $this->singleton('router', function (ContainerInterface $container) {
                ///
            });
        }

        /**
         * Register the not found request handler
         */
        if (!$this->has('notFoundHandler')) {
            $this->register(\Framework\Http\Handlers\NotFoundHandler::class);
            $this->alias('notFoundHandler', \Framework\Http\Handlers\NotFoundHandler::class);
        }
        
        /**
         * Register the request error handler
         */
        if (!$this->has('errorHandler')) {
            $this->implemented(
                \Framework\Http\Handlers\ErrorRequestHandlerInterface::class,
                \Framework\Http\Handlers\ErrorRequestHandler::class
            );
            $this->alias(
                \Framework\Http\Handlers\ErrorRequestHandler::class, 
                \Framework\Http\Handlers\ErrorRequestHandlerInterface::class
            );
            $this->alias('errorHandler', \Framework\Http\Handlers\ErrorRequestHandlerInterface::class);
        }
    }

    private function bootServices()
    {
        if (isset($this->settings['boot.services.file'])) {
            require $this->settings['boot.services.file'];
        }
    }
}
