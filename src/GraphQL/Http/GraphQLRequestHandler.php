<?php

declare(strict_types=1);

namespace Framework\GraphQL\Http;

use Exception;
use RuntimeException;
use Framework\Http\Body;
use GraphQL\Error\Debug;
use GraphQL\Error\FormattedError;
use function Framework\Http\response;

use Framework\Http\ResponseStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
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
     * Json encoding option for response body
     *
     * @var int
     */
    protected $jsonEncodingOption;

    /**
     * Create a new graphql server handler instance
     *
     * @param GraphQLServerInterface $graphqlServer
     * @param int $debug
     */
    public function __construct(
        GraphQLServerInterface $graphqlServer,
        int $debug = 0,
        int $jsonEncodingOption = JSON_PRETTY_PRINT
    ) {
        $this->jsonEncodingOption = $jsonEncodingOption;
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

        return $this->buildResponse($responseStatusCode, $output);

        // return response(
        //     $responseStatusCode, 
        //     json_encode($output, $this->jsonEncodingOption), 
        //     ['Content-Type' => 'Content-Type: application/json; charset=UTF-8']
        // );
    }

    protected function buildResponse(int $status, $data = []): ResponseInterface
    {
        $json = json_encode($data, $this->jsonEncodingOption);
        if ($json === false) {
            throw new RuntimeException(json_last_error_msg(), json_last_error());
        }

        $body = new Body('php://temp', 'r+');
        $body->rewind();
        $body->truncate(0);
        $body->write($json);
        
        return response()->withBody($body)
            ->withHeader('Content-Type', 'application/json;charset=utf-8')
            ->withStatus($status);
    }
}
