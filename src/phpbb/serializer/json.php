<?php

namespace phpbb\serializer;

use phpbb\response;

class json extends abstraction
{
    protected response $response;

    public function __toString(): string
    {
        if ($this->response->body !== null) {
            return json_encode($this->response->body, JSON_PRETTY_PRINT);
        }
        return '';
    }
}