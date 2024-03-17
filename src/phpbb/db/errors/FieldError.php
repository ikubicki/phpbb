<?php

namespace phpbb\db\errors;

class FieldError extends DatabaseError
{
    const UNDEFINED_DATA_TYPE = 'Undefined data type format: %s.';
    const NOT_WRITABLE = 'Field %s is not writable.';

    public string $error = '';

    public function __construct(string $message, int $code = 500)
    {
        $this->error = $message;
        parent::__construct('Database field error: ' . $message, $code);
    }
}