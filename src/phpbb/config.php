<?php

namespace phpbb;

class config extends config\abstraction
{
    protected $data;
    public function __construct($file) {
        $this->data = config\loader::load($file);
    }
}