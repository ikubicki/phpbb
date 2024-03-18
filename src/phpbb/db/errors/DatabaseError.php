<?php

namespace phpbb\db\errors;

class DatabaseError extends \RuntimeException
{
    const NO_DATABASE_CONNECTION = 'Configuration for %s database connection not found.';
    const DATABASE_NOT_SUPPORTED = '%s database type is not supported.';
    const DATABASE_NOT_INSTALLED = 'Cannot find %s database driver.';
    const DATABASE_NOT_SELECTED = 'Database name not specified.';

    public function __construct($message, $code = 500)
    {
        parent::__construct('Database error: ' . $message, $code);
    }
}