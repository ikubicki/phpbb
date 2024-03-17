<?php

namespace phpbb\request;

/**
 * HTTP client class
 */
class client
{
    /**
     * @var string $authorization
     */
    public string $authorization;

    /**
     * @var ?string $ip
     */
    public ?string $ip;

    /**
     * @var ?string $userAgent
     */
    public ?string $userAgent;

    /**
     * The constructor
     * 
     * @author ikubicki
     */
    public function __construct()
    {
        $this->authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $this->ip = $_SERVER['HTTP_TRUE_CLIENT_IP'] ?? 
            $_SERVER['HTTP_X_REAL_IP'] ?? 
            $_SERVER['REMOTE_ADDR'] ?? 
            trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')[0]) ?? null;
    }

    /**
     * Returns authorization bearer
     * 
     * @author ikubicki
     * @return ?string
     */
    public function bearer(): ?string {
        if (stripos($this->authorization, 'bearer ') === false) {
            return null;
        }
        return substr($this->authorization, 7);
    }
}