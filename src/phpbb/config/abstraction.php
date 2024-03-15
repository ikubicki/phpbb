<?php

namespace phpbb\config;

abstract class abstraction
{

    protected $data;
    
    public function isMissing(): bool {
        return $this->data === false;
    }

    public function raw(string $property)
    {
        return $this->data[$property] ?? false;
    }

    public function get(string $property, $alternative = null)
    {
        if ($alternative === null) {
            $alternative = new item();
        }
        if (!property_exists($this->data, $property)) {
            return $alternative;
        }
        if (is_object($this->data->$property)) {
            return new item($this->data->$property);
        }
        return $this->data->$property;
    }

    public function set(string $property, $value)
    {
        $this->data[$property] = $value;
        return $this;
    }
}