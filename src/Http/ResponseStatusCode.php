<?php

namespace Pgraph\Http;

/**
 * HTTP Response Status Codes
 * 
 * Note: Extracted from Slim
 */
class ResponseStatusCode
{
    const CONTINUE = 100;
    const SWITCHING_PROTOCOLS = 101;
    const PROCESSING = 102;
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NONAUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;
    const MULTI_STATUS = 207;
    const ALREADY_REPORTED = 208;
    const IM_USED = 226;
    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;
    const UNUSED= 306;
    const TEMPORARY_REDIRECT = 307;
    const PERMANENT_REDIRECT = 308;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED  = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT = 408;
    const CONFLICT = 409;
    const GONE = 410;
    const LENGTH_REQUIRED = 411;
    const PRECONDITION_FAILED = 412;
    const REQUEST_ENTITY_TOO_LARGE = 413;
    const REQUEST_URI_TOO_LONG = 414;
    const UNSUPPORTED_MEDIA_TYPE = 415;
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const EXPECTATION_FAILED = 417;
    const IM_A_TEAPOT = 418;
    const MISDIRECTED_REQUEST = 421;
    const UNPROCESSABLE_ENTITY = 422;
    const LOCKED = 423;
    const FAILED_DEPENDENCY = 424;
    const UPGRADE_REQUIRED = 426;
    const PRECONDITION_REQUIRED = 428;
    const TOO_MANY_REQUESTS = 429;
    const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    const CONNECTION_CLOSED_WITHOUT_RESPONSE = 444;
    const UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    const CLIENT_CLOSED_REQUEST = 499;
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
    const GATEWAY_TIMEOUT = 504;
    const VERSION_NOT_SUPPORTED = 505;
    const VARIANT_ALSO_NEGOTIATES = 506;
    const INSUFFICIENT_STORAGE = 507;
    const LOOP_DETECTED = 508;
    const NOT_EXTENDED = 510;
    const NETWORK_AUTHENTICATION_REQUIRED = 511;
    const NETWORK_CONNECTION_TIMEOUT_ERROR = 599;
    
    /**
     * Defaults response status reason phrases
     */
    const STATUS_REASON_PHRASES = [
        ResponseStatusCode::CONTINUE => 'Continue',
        ResponseStatusCode::SWITCHING_PROTOCOLS => 'Switching Protocols',
        ResponseStatusCode::PROCESSING => 'Processing',
        ResponseStatusCode::OK => 'OK',
        ResponseStatusCode::CREATED => 'Created',
        ResponseStatusCode::ACCEPTED => 'Accepted',
        ResponseStatusCode::NONAUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        ResponseStatusCode::NO_CONTENT => 'No Content',
        ResponseStatusCode::RESET_CONTENT => 'Reset Content',
        ResponseStatusCode::PARTIAL_CONTENT => 'Partial Content',
        ResponseStatusCode::MULTI_STATUS => 'Multi-Status',
        ResponseStatusCode::ALREADY_REPORTED => 'Already Reported',
        ResponseStatusCode::IM_USED => 'IM Used',
        ResponseStatusCode::MULTIPLE_CHOICES => 'Multiple Choices',
        ResponseStatusCode::MOVED_PERMANENTLY => 'Moved Permanently',
        ResponseStatusCode::FOUND => 'Found',
        ResponseStatusCode::SEE_OTHER => 'See Other',
        ResponseStatusCode::NOT_MODIFIED => 'Not Modified',
        ResponseStatusCode::USE_PROXY => 'Use Proxy',
        ResponseStatusCode::UNUSED => '(Unused)',
        ResponseStatusCode::TEMPORARY_REDIRECT => 'Temporary Redirect',
        ResponseStatusCode::PERMANENT_REDIRECT => 'Permanent Redirect',
        ResponseStatusCode::BAD_REQUEST => 'Bad Request',
        ResponseStatusCode::UNAUTHORIZED => 'Unauthorized',
        ResponseStatusCode::PAYMENT_REQUIRED => 'Payment Required',
        ResponseStatusCode::FORBIDDEN => 'Forbidden',
        ResponseStatusCode::NOT_FOUND => 'Not Found',
        ResponseStatusCode::METHOD_NOT_ALLOWED => 'Method Not Allowed',
        ResponseStatusCode::NOT_ACCEPTABLE => 'Not Acceptable',
        ResponseStatusCode::PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        ResponseStatusCode::REQUEST_TIMEOUT => 'Request Timeout',
        ResponseStatusCode::CONFLICT => 'Conflict',
        ResponseStatusCode::GONE => 'Gone',
        ResponseStatusCode::LENGTH_REQUIRED => 'Length Required',
        ResponseStatusCode::PRECONDITION_FAILED => 'Precondition Failed',
        ResponseStatusCode::REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
        ResponseStatusCode::REQUEST_URI_TOO_LONG => 'Request-URI Too Long',
        ResponseStatusCode::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        ResponseStatusCode::REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        ResponseStatusCode::EXPECTATION_FAILED => 'Expectation Failed',
        ResponseStatusCode::IM_A_TEAPOT => 'I\'m a teapot',
        ResponseStatusCode::MISDIRECTED_REQUEST => 'Misdirected Request',
        ResponseStatusCode::UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
        ResponseStatusCode::LOCKED => 'Locked',
        ResponseStatusCode::FAILED_DEPENDENCY => 'Failed Dependency',
        ResponseStatusCode::UPGRADE_REQUIRED => 'Upgrade Required',
        ResponseStatusCode::PRECONDITION_REQUIRED => 'Precondition Required',
        ResponseStatusCode::TOO_MANY_REQUESTS => 'Too Many Requests',
        ResponseStatusCode::REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        ResponseStatusCode::CONNECTION_CLOSED_WITHOUT_RESPONSE => 'Connection Closed Without Response',
        ResponseStatusCode::UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
        ResponseStatusCode::CLIENT_CLOSED_REQUEST => 'Client Closed Request',
        ResponseStatusCode::INTERNAL_SERVER_ERROR => 'Internal Server Error',
        ResponseStatusCode::NOT_IMPLEMENTED => 'Not Implemented',
        ResponseStatusCode::BAD_GATEWAY => 'Bad Gateway',
        ResponseStatusCode::SERVICE_UNAVAILABLE => 'Service Unavailable',
        ResponseStatusCode::GATEWAY_TIMEOUT => 'Gateway Timeout',
        ResponseStatusCode::VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported',
        ResponseStatusCode::VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
        ResponseStatusCode::INSUFFICIENT_STORAGE => 'Insufficient Storage',
        ResponseStatusCode::LOOP_DETECTED => 'Loop Detected',
        ResponseStatusCode::NOT_EXTENDED => 'Not Extended',
        ResponseStatusCode::NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
        ResponseStatusCode::NETWORK_CONNECTION_TIMEOUT_ERROR => 'Network Connect Timeout Error'
    ];
}
