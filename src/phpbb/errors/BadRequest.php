<?php

namespace phpbb\errors;

use phpbb\response;

class BadRequest extends \LogicException
{
    public function __construct($message, $code = response::BAD_REQUEST)
    {
        parent::__construct($message, $code);
    }
}