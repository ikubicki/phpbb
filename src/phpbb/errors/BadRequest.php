<?php

namespace phpbb\errors;

use phpbb\response;

class BadRequest extends \LogicException
{

    const FIELDS_VALUES_TAKEN = 'Values for fields [%s] are already taken';
    
    /**
     * The constructor
     * 
     * @author ikubicki
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message, int $code = response::BAD_REQUEST)
    {
        parent::__construct($message, $code);
    }
}