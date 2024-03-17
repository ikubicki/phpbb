<?php

namespace phpbb\serializer;

use phpbb\response;

/**
 * Serializer abstraction class
 */
abstract class abstraction
{

    /**
     * @var response $response
     */
    protected response $response;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param response $response
     */
    public function __construct(response $response)
    {
        $this->response = $response;    
    }

    /**
     * String serializer
     * 
     * @author ikubicki
     * @return string
     */
    abstract public function __toString(): string;
}