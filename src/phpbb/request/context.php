<?php

namespace phpbb\request;

/**
 * Request context class
 * An object to transfer context information between methods within a single request
 */
class context
{
    /**
     * @var array $data
     */
    private static array $data = [];

    /**
     * Stores property
     * 
     * @author ikubicki
     * @param string $property
     * @param mixed $value
     * @return context
     */
    public function set(string $property, mixed $value): context
    {
        self::$data[$property] = $value;
        return $this;
    }

    /**
     * Returns stored property
     * 
     * @author ikubicki
     * @param string $property
     * @param mixed $alternative
     * @return mixed
     */
    public function get(string $property, mixed $alternative = null): mixed
    {
        return self::$data[$property] ?? $alternative;
    }
}