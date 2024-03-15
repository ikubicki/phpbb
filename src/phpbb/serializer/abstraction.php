<?php

namespace phpbb\serializer;

use phpbb\response;

abstract class abstraction
{

    protected response $response;

    public function __construct(response $response)
    {
        $this->response = $response;    
    }

    abstract public function __toString();
}