<?php

namespace phpbb\config;

use stdClass;

/**
 * Config value wrapper class
 */
class item extends abstraction
{

    /**
     * @var ?stdClass $data
     */
    protected ?stdClass $data;

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
}