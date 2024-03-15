<?php

namespace phpbb\config;

class item extends abstraction
{
    protected $data;

    public function __construct($data = null)
    {
        $this->data = $data;
    }
}