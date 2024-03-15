<?php

namespace phpbb\request;

class context
{
    private static array $data = [];

    public function set(string $property, $value): context
    {
        self::$data[$property] = $value;
        return $this;
    }

    public function get(string $property, $alternative = null)
    {
        return self::$data[$property] ?? $alternative;
    }
}