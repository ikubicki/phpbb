<?php

namespace phpbb\core;

use stdClass;

/**
 * Key value store class
 */
class keyvalue
{
    /**
     * @var ?stdClass $data
     */
    private ?stdClass $data;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param ?stdClass $data
     */
    public function __construct(?stdClass $data = null)
    {
        $this->data = $data;
    }

    /**
     * Import data into key value store
     * 
     * @author ikubicki
     * @param ?stdClass $data
     * @return keyvalue
     */
    public function import(?stdClass $data): keyvalue
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Sets the property
     * 
     * @author ikubicki
     * @param string $property
     * @param mixed $value
     * @return keyvalue
     */
    public function set(string $property, mixed $value): keyvalue
    {
        $this->data->$property = $value;
        return $this;
    }

    /**
     * Returns raw value of the property
     * 
     * @author ikubicki
     * @param string $property
     * @param mixed $alternative
     * @return mixed
     */
    public function raw(string $property, mixed $alternative = null): mixed
    {
        return $this->data->$property ?? $alternative;
    }

    /**
     * Returns value of the property
     * Wraps value on object or null
     * 
     * @author ikubicki
     * @param string $property
     * @param mixed $alternative
     * @return mixed
     */
    public function get(string $property, mixed $alternative = null): mixed
    {
        if (!property_exists($this->data, $property)) {
            return $alternative ?? new keyvalue();
        }
        if ($this->data->$property instanceof stdClass) {
            return new keyvalue($this->data->$property);
        }
        return $this->data->$property;
    }
}