<?php

namespace phpbb\request;

/**
 * URL container
 */
class url
{
    /**
     * @var string $host
     */
    public string $host;

    /**
     * @var string $path
     */
    public string $path;

    /**
     * @var string $hostname
     */
    public string $hostname;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param string $host
     * @param string $path
     * @param string $hostname
     */
    public function __construct(string $host, string $path, string $hostname)
    {
        $this->host = $host;
        $this->path = $path;
        $this->hostname = $hostname;
    }

    /**
     * String serializer
     * 
     * @author ikubicki
     * @return string
     */
    public function __toString(): string
    {
        return $this->host . $this->path;
    }
}