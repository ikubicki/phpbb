<?php

namespace phpbb\serializer;

use phpbb\response;

class stream extends abstraction
{

    protected response $response;

    public function __toString(): string
    {
        return json_encode($this->response->body);
    }
}