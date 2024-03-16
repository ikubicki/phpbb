<?php

namespace phpbb\errors;

class ServerError extends \RuntimeException
{
    const NO_DATABASE_CONNECTION = 'Configuration for %s database connection not found.';
    const DATABASE_NOT_SUPPORTED = '%s database type is not supported.';
    const PROVIDE_INSERT_COLLECTION = 'Bulk insert operation requires a collection (array) of fields set.';
    const UNDEFINED_DATA_FORMAT = 'Undefined data type format: %s.';
    const DATABASE_NOT_INSTALLED = 'Cannot find %s database driver.';
}