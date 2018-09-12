<?php

declare(strict_types=1);

namespace Framework\GraphQL;

use Exception;
use GraphQL\Server\StandardServer;
use Framework\Http\ResponseStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Framework\Http\jsonResponse;

/**
 * Handle request for the GraphQL server.
 */
class GraphQLRequestHandler implements RequestHandlerInterface
{
    /**
     * GraphQL server instance
     *
     * @var StandardServer
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
     * @param StandardServer $graphqlServer
     * @param bool $debug
     */
    public function __construct(StandardServer $graphqlServer, bool $debug = false)
    {
        $this->graphqlSerber = $graphqlServer;
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
            $output = $this->graphqlServer->executePsrRequest($request);
        } catch (Exception $error) {
            $output = [
                'errors' => [
                    FormattedError::createFromException($error, $this->debug)
                ]
            ];
            $responseStatusCode = ResponseStatusCode::INTERNAL_SERVER_ERROR;
        }
        return jsonResponse($output, $responseStatusCode);
    }
}
