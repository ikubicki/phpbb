<?php

namespace phpbb\errors;

class ServerError extends \RuntimeException
{
    const PROVIDE_INSERT_COLLECTION = 'Bulk insert operation requires a collection (array) of fields set.';
}