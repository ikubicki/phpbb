<?php

namespace phpbb\core;

use JsonSerializable;
use stdClass;

/**
 * Key value store class
 */
class keyvalue implements JsonSerializable
{
    /**
     * @var ?stdClass $data
     */
    private stdClass|array|null $data;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param stdClass|array|null $data
     */
    public function __construct(stdClass|array|null $data = null)
    {
        $this->data = $data;
    }

    /**
     * Import data into key value store
     * 
     * @author ikubicki
     * @param stdClass|array|null $data
     * @return keyvalue
     */
    public function import(stdClass|array|null $data): keyvalue
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Exports stored data
     * 
     * @author ikubicki
     * @return mixed
     */
    public function export(): mixed
    {
        return $this->data;
    }

    /**
     * Checks if data is an array
     * 
     * @author ikubicki
     * @return bool
     */
    public function isArray(): bool
    {
        return is_array($this->data);
    }

    /**
     * JSON serialization method
     * 
     * @author ikubicki
     * @return array
     */
    public function jsonSerialize(): array
    {
        return (array) $this->data;
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