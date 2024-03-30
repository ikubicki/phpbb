<?php

namespace phpbb\db\errors;

class DuplicateError extends DatabaseError
{
    
    /**
     * @var string $error
     */
    public string $error = '';

    /**
     * @var array $fields
     */
    public array $fields = [];

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param string $message
     * @param array $fields
     * @param int $code
     */
    public function __construct(string $message, array $fields, int $code = 500)
    {
        $this->error = $message;
        $this->fields = $fields;
        parent::__construct('Duplicate error: ' . $message, $code);
    }
}