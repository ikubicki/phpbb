<?php

namespace phpbb\request;

use phpbb\core\keyvalue;

class uri extends keyvalue
{

    /**
     * Return URI parameter
     * 
     * @author ikubicki
     * @param string $parameter
     * @param mixed $alternative
     * @return mixed
     */
    public function param(string $parameter, mixed $alternative = null): mixed
    {
        return $this->raw($parameter, $alternative);
    }
}