<?php

namespace phpbb\config;

use stdClass;

/**
 * Abstracted configuration class
 */
abstract class abstraction
{

    /**
     * @var ?stdClass $data
     */
    protected ?stdClass $data;
    
    /**
     * Checks if data is missing
     * 
     * @author ikubicki
     * @return bool
     */
    public function isMissing(): bool
    {
        return $this->data === false;
    }

    /**
     * Returns raw config value
     * 
     * @author ikubicki
     * @param string $property
     * @param mixed $alternative
     * @return mixed
     */
    public function raw(string $property, mixed $alternative = null): mixed
    {
        if (!property_exists($this->data, $property)) {
            return $alternative;
        }
        return $this->data->$property;
    }

    /**
     * Returns wrapped config value
     * If value is not set or value is an object,
     * an empty config entity will be returned.
     * 
     * @author ikubicki
     * @param string $property
     * @param mixed $alternative
     * @return mixed
     */
    public function get(string $property, mixed $alternative = null): mixed
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

    /**
     * Sets config value
     * 
     * @author ikubicki
     * @param string $property
     * @param mixed $value
     * @return abstraction
     */
    public function set(string $property, mixed $value): abstraction
    {
        $this->data[$property] = $value;
        return $this;
    }
}