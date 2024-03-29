<?php

namespace phpbb\request;

/**
 * HTTP information class
 */
class http
{

    /**
     * @var string $host
     */
    public string $host;

    /**
     * @var ?int $port
     */
    public ?int $port;

    /**
     * @var string $path
     */
    public string $path;

    /**
     * @var bool $ssl
     */
    public bool $ssl;

    /**
     * @var ?string $referer
     */
    public ?string $referer;

    /**
     * @var string $query
     */
    public string $query;

    /**
     * @var string $base
     */
    public string $base;

    /**
     * The constructor
     * 
     * @author ikubicki
     */
    public function __construct()
    {
        $this->host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $this->port = $_SERVER['HTTP_X_FORWARDED_PORT'] ?? $_SERVER['SERVER_PORT'] ?? null;
        $this->path = $_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'] ?? $_SERVER['REDIRECT_URL'] ?? '/';
        if (($_SERVER['HTTP_X_FORWARDED_PATH'] ?? false) && ($_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? false)) {
            $this->path = substr($_SERVER['HTTP_X_FORWARDED_PATH'], strlen($_SERVER['HTTP_X_FORWARDED_PREFIX']) - 1);
        }
        
        $this->ssl = (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['REQUEST_SCHEME'] ?? '') === 'https') ||
            stripos($_SERVER['HTTPS'] ?? '', 'On') === 0;
        $this->referer = $_SERVER['HTTP_REFERER'] ?? null;
        $this->query = $_SERVER['QUERY_STRING'] ?? '';

        $this->base = $this->getUrl($_SERVER['SCRIPT_NAME']);
        if ($_SERVER['PATH_INFO'] ?? false) {
            $uri = $_SERVER['REQUEST_URI'];
            $this->base = $this->getUrl(substr($uri, 0, strpos($uri, $_SERVER['PATH_INFO'])));
        }
    }

    public function getUrl(string $path = ''): string
    {
        $protocol = $_SERVER['REQUEST_SCHEME'];
        $host = $_SERVER['HTTP_HOST'];
        return sprintf('%s://%s%s', $protocol, $host, $path);
    }
}