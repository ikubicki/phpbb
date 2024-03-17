<?php

namespace phpbb\serializer;

use phpbb\response;

/**
 * JSON serializer
 */
class json extends abstraction
{
    /**
     * @var response $response
     */
    protected response $response;

    /**
     * String serializer
     * 
     * @author ikubicki
     * @return string
     */
    public function __toString(): string
    {
        if ($this->response->body !== null) {
            return json_encode($this->response->body, JSON_PRETTY_PRINT);
        }
        return '';
    }
}