<?php

namespace phpbb\apps\router;

use Closure;
use phpbb\app;
use phpbb\config;
use phpbb\request;
use phpbb\response;
use stdClass;
use Throwable;

class error implements route
{

    /**
     * @var Closure $callback
     */
    private Closure $callback;

    /**
     * @var Throwable $throwable
     */
    private Throwable $throwable;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param Throwable $throwable
     */
    public function __construct(Throwable $throwable)
    {
        $this->throwable = $throwable;
        $this->callback = function($request, $response) use ($throwable) {
            $code = $throwable->getCode() ?: $response::SERVER_ERROR;
            $result = [
                'error' => $throwable->getMessage(),
            ];
            if (config::env('LOG_LEVEL') == config::LOG_LEVEL_DEBUG) {
                $result['trace'] = $throwable->getTraceAsString();
            }
            return $response->status($code)->send($result);
        };
    }

    /**
     * Tests if route matches requested path
     * Returns object containing path parameters or null on failure
     * 
     * @author ikubicki
     * @param string $path
     * @return ?stdClass
     */
    public function test(string $path): ?stdClass
    {
        return new stdClass;
    }

    /**
     * Executes the route callback
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @param response $previous
     * @return ?response
     */
    public function execute(request $request, response $response, app $app, ?response $previous): ?response
    {
        return call_user_func_array($this->callback, [
            $request, $response, $app, $previous
        ]);
    }
}