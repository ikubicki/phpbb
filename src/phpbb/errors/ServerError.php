<?php

namespace phpbb\errors;

class ServerError extends \RuntimeException
{
    const CONFIG_MISSING = 'Configuration file is missing: %s';
    const CONFIG_INVALID_JSON = 'Configuration file is not a valid JSON file: %s';
}