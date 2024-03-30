<?php

namespace phpbb\apps\router;

use phpbb\app;
use phpbb\request;
use phpbb\response;
use stdClass;

interface route
{

    /**
     * Tests if route matches requested path
     * Returns object containing path parameters or null on failure
     * 
     * @author ikubicki
     * @param string $path
     * @return ?stdClass
     */
    public function test(string $path): ?stdClass;

    /**
     * Executes the route callback
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @param ?response $previous
     * @return ?response
     */
    public function execute(request $request, response $response, app $app, ?response $previous): ?response;
}