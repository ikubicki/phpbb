<?php

namespace phpbb\serializer;

use phpbb\response;

/**
 * Stream/binary serializer
 */
class stream extends abstraction
{

    /**
     * @var response $response
     */
    protected response $response;

    /**
     * Stream/binary serializer
     * 
     * @author ikubicki
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->response->body;
    }
}