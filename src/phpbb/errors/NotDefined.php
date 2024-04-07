<?php

namespace phpbb\errors;

use phpbb\response;
use RuntimeException;

class NotDefined extends RuntimeException
{

    const NO_CALLBACK = 'Route %s %s does not have a callback defined.';

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message, response::SERVER_ERROR);
    }
}