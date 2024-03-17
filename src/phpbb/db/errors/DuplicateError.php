<?php

namespace phpbb\db\errors;

class DuplicateError extends DatabaseError
{
    
    public string $error = '';

    public function __construct($message, $code = 500)
    {
        $this->error = $message;
        parent::__construct('Duplicate error: ' . $message, $code);
    }
}