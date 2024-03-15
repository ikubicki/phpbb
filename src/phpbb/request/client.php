<?php

namespace phpbb\request;

class client
{
    public string $authorization;
    public ?string $ip;
    public ?string $userAgent;

    public function __construct()
    {
        $this->authorization = $_SERVER['AUTHORIZATION'] ?? '';
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $this->ip = $_SERVER['HTTP_TRUE_CLIENT_IP'] ?? 
            $_SERVER['HTTP_X_REAL_IP'] ?? 
            $_SERVER['REMOTE_ADDR'] ?? 
            trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')[0]) ?? null;
    }

    public function getBearer(): string|bool {
        if (stripos($this->authorization, 'bearer ')) {
            return substr($this->authorization, 7);
        }
        return false;
    }
}