<?php

namespace phpbb\request;

class http
{

    public string $host;
    public ?int $port;
    public string $path;
    public bool $ssl;
    public ?string $referer;
    public string $query;

    public function __construct()
    {
        $this->host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        $this->port = $_SERVER['HTTP_X_FORWARDED_PORT'] ?? $_SERVER['SERVER_PORT'] ?? null;
        $this->path = $_SERVER['REQUEST_URI'] ?? $_SERVER['REDIRECT_URL'] ?? '/';
        if (($_SERVER['HTTP_X_FORWARDED_PATH'] ?? false) && ($_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? false)) {
            $this->path = substr($_SERVER['HTTP_X_FORWARDED_PATH'], strlen($_SERVER['HTTP_X_FORWARDED_PREFIX']) - 1);
        }
        $this->ssl = (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['REQUEST_SCHEME'] ?? '') === 'https') ||
            stripos($_SERVER['HTTPS'] ?? '', 'On') === 0;
        $this->referer = $_SERVER['HTTP_REFERER'] ?? null;
        $this->query = $_SERVER['QUERY_STRING'] ?? '';
    }

}