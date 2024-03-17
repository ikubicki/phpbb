<?php

namespace phpbb\errors;

use phpbb\request;
use phpbb\response;

class ResourceNotFound extends \LogicException
{
    public function __construct(request $request)
    {
        parent::__construct(
            sprintf('Resource for %s not found', $request->http->path), 
            response::NOT_FOUND
        );
    }
}