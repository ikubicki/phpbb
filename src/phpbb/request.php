<?php

namespace phpbb;

use phpbb\request\context;

class request
{

    public ?string $accept;
    public ?string $acceptEncoding;
    public ?string $acceptLanguage;
    public request\client $client;
    public request\http $http;
    public string $method;
    public ?string $contentType;
    public ?string $contentLength;
    public array $params = [];
    public context $context;

    public function __construct()
    {
        $this->accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $this->acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
        $this->acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $this->client = new request\client();
        $this->http = new request\http();
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->contentType = $_SERVER['Content-Type'] ?? null;
        $this->contentLength = $_SERVER['Content-Length'] ?? null;
        $this->context = new context();
    }

    public function useGzip(): bool
    {
        return strpos($this->acceptEncoding, 'gzip') !== false;
    }

    public function isJson(): bool
    {
        return stripos($this->contentType, 'application/json') !== false;
    }

    public function isXml(): bool
    {
        return stripos($this->contentType, 'application/xml') !== false;
    }

    public function body(bool $unserialize = true)
    {
        $contents = file_get_contents('php://input');
        if ($contents && $unserialize) {
            if ($this->isJson()) {
                return json_decode($contents);
            }
            if ($this->isXml()) {
                return simplexml_load_string($contents);
            }
        }
        return $contents;
    }

    public function param(string $parameter): string|array|bool
    {
        return $this->params[$parameter] ?? false;
    }

    public function get(string $property): string|array|bool
    {
        return $_GET[$property] ?? false;
    }

    public function post(string $property): string|array|bool
    {
        return $_POST[$property] ?? false;
    }

    public function cookie(string $property): string|bool
    {
        return $_COOKIE[$property] ?? false;
    }

    public function file(string $name): array|bool
    {
        return $_FILES[$name] ?? false;
    }

    public function header(string $name): string|bool
    {
        return $_SERVER['HTTP_' . str_replace(['-', '.'], '_', strtoupper($name))] ?? false;
    }

    public function bearer()
    {
        return substr($this->header('Authorization'), 7);
    }

    public function context(string $property, $alternative = null)
    {
        return $this->context->get($property, $alternative);
    }
}