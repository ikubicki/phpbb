<?php

namespace phpbb;

use phpbb\request\context;
use phpbb\request\uri;
use stdClass;

class request
{

    /**
     * @var ?string $accept
     */
    public ?string $accept;

    /**
     * @var ?string $acceptEncoding
     */
    public ?string $acceptEncoding;

    /**
     * @var ?string $acceptLanguage
     */
    public ?string $acceptLanguage;

    /**
     * @var request\client $client
     */
    public request\client $client;

    /**
     * @var request\http $http
     */
    public request\http $http;

    /**
     * @var string $method
     */
    public string $method;

    /**
     * @var ?string $contentType
     */
    public ?string $contentType;

    /**
     * @var ?string $contentLength
     */
    public ?string $contentLength;

    /**
     * @var request\body $body
     */
    public request\body $body;

    /**
     * @var uri $uri
     */
    public uri $uri;

    /**
     * @var context $context
     */
    public context $context;

    /**
     * The constructor
     * Wraps request data into object
     * 
     * @author ikubicki
     */
    public function __construct()
    {
        $this->accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $this->acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
        $this->acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $this->client = new request\client();
        $this->http = new request\http();
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->contentType = $_SERVER['CONTENT_TYPE'] ?? 'application/json';
        $this->contentLength = $_SERVER['CONTENT_LENGTH'] ?? null;
        $this->body = new request\body($this);
        $this->context = new context(new stdClass());
        $this->uri = new uri();
    }

    /**
     * Checks if gzip is accepted by client
     * 
     * @author ikubicki
     * @return bool
     */
    public function useGzip(): bool
    {
        return strpos($this->acceptEncoding, 'gzip') !== false;
    }

    /**
     * Checks if posted data is a JSON data
     * 
     * @author ikubicki
     * @return bool
     */
    public function isJson(): bool
    {
        return stripos($this->contentType, 'application/json') !== false;
    }

    /**
     * Checks if posted data is a XML data
     * 
     * @author ikubicki
     * @return bool
     */
    public function isXml(): bool
    {
        return stripos($this->contentType, 'application/xml') !== false;
    }

    /**
     * Returns query parameter value
     * 
     * @author ikubicki
     * @param string $parameter
     * @return ?string
     */
    public function query(string $property): ?string
    {
        return $_GET[$property] ?? null;
    }

    /**
     * Returns POST form parameter value
     * 
     * @author ikubicki
     * @param string $parameter
     * @param mixed $alternative
     * @return mixed
     */
    public function post(string $property, mixed $alternative = null): mixed
    {
        return $_POST[$property] ?? $alternative;
    }

    /**
     * Returns cookie value
     * 
     * @author ikubicki
     * @param string $name
     * @return ?string
     */
    public function cookie(string $name): ?string
    {
        return $_COOKIE[str_replace(['-', '.'], '_', $name)] ?? null;
    }

    /**
     * Returns posted file
     * 
     * @author ikubicki
     * @param string $name
     * @return ?array
     */
    public function file(string $name): ?array
    {
        return $_FILES[$name] ?? null;
    }

    /**
     * Returns header value
     * 
     * @author ikubicki
     * @param string $name
     * @return ?string
     */
    public function header(string $name): ?string
    {
        return $_SERVER['HTTP_' . str_replace(['-', '.'], '_', strtoupper($name))] ?? null;
    }

    /**
     * Returns instance of request context
     * 
     * @author ikubicki
     * @param string $property
     * @param mixed $alternative
     * @return mixed
     */
    public function context(string $property, mixed $alternative = null): mixed
    {
        return $this->context->get($property, $alternative);
    }
}