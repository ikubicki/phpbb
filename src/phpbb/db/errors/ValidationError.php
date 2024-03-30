<?php

namespace phpbb\db\errors;

class ValidationError extends \LogicException
{

    const LENGTH = 'Field \'%s\' must be at least %d characters long.';
    
    public string $error = '';

    public function __construct($message, $code = 400)
    {
        $this->error = $message;
        parent::__construct('Field validation error: ' . $message, $code);
    }
}