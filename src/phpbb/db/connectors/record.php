<?php

namespace phpbb\db\connectors;

use stdClass;

class record
{

    public stdClass $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function hydrate($hydrator)
    {
        return call_user_func($hydrator, $this->data);
    }
}