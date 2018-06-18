<?php

namespace Framework\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * A PSR-7 ResponseInterface implementation
 */
class Response extends Message implements ResponseInterface
{
    /**
     * The HTTP response status code
     *
     * @var int
     */
    protected $statusCode = ResponseStatusCode::OK;

    /**
     * The HTTP response status reason phrase
     *
     * @var string
     */
    protected $reasonPhrase = ResponseStatusCode::STATUS_REASON_PHRASES[ResponseStatusCode::OK];

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Verfies if the given status code is valid
     *
     * @param int $code
     * @return boolean
     */
    protected function isValidStatusCode($code)
    {
        return is_int($code) && array_key_exists($code, ResponseStatusCode::STATUS_REASON_PHRASES);
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        if (!$this->isValidStatusCode($code)) {
            throw new \InvalidArgumentException($code . ' is not a valid response status code.');
        }
        if (!$reasonPhrase) {
            $reasonPhrase = ResponseStatusCode::STATUS_REASON_PHRASES[$code];
        }
        $clone = clone $this;
        $clone->code = $code;
        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase ?: '';
    }

    /**
     * Returns an instance with JSON encoded body
     *
     * Note: this method is not part of the PSR-7 specification
     * 
     * @param mixed $data
     * @param int $statusCode
     * @param int $encodingOpts
     * @return static
     * @throws \RuntimeException if data cannot be encoded correctly
     */
    public function withJson($data, int $statusCode = ResponseStatusCode::OK, $encodingOpts = 0)
    {
        $json = json_encode($data, $encodingOpts);
        if ($json === false) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }
        $clone = $this->withBody(new Body('php://temp', 'r+'))
                      ->withHeader('Content-Type', 'application/json;charset=utf-8')
                      ->withStatus($statusCode);
        $clone->body->write($json);

        return $clone;
    }
}
