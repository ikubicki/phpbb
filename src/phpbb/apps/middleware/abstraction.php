<?php

namespace phpbb\apps\middleware;

use phpbb\app;
use phpbb\request;
use phpbb\response;

/**
 * Middleware abstraction
 */
abstract class abstraction
{
    /**
     * Executes the middleware
     * 
     * @abstract
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return request
     */
    abstract public function execute(request $request, response $response, app $app): request;
}