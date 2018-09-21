<?php

declare(strict_types=1);

namespace Framework\GraphQL\Http;

use Exception;
use GraphQL\Error\FormattedError;
use Framework\Http\ResponseStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Framework\Http\response;
use Framework\Http\Handlers\HasMiddlewareTrait;

/**
 * Handle request for the GraphQL server.
 */
class GraphQLRequestHandler implements RequestHandlerInterface
{
    use HasMiddlewareTrait;
    
    /**
     * GraphQL server instance
     *
     * @var GraphQLServerInterface
     */
    protected $graphqlServer;

    /**
     * Define debug schema errors
     *
     * @var bool
     */
    protected $debug;

    /**
     * Create a new graphql server handler instance
     *
     * @param GraphQLServerInterface $graphqlServer
     * @param bool $debug
     */
    public function __construct(GraphQLServerInterface $graphqlServer, bool $debug = false)
    {
        $this->graphqlServer = $graphqlServer;
        $this->debug = $debug;
    }

    /**
     * Handle a GraphQL request.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $responseStatusCode = ResponseStatusCode::OK;
        try {
            $output = $this->graphqlServer->execute($request);
        } catch (Exception $error) {
            $output = [
                'errors' => [
                    FormattedError::createFromException($error, $this->debug)
                ]
            ];
            $responseStatusCode = ResponseStatusCode::INTERNAL_SERVER_ERROR;
        }
        return response()->withJson($output, $responseStatusCode, JSON_PRETTY_PRINT);
    }
}
