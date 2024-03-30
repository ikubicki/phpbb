<?php

namespace phpbb\apps\router;

use phpbb\app;
use phpbb\request;
use phpbb\response;
use stdClass;

class defined implements route
{
    
    /**
     * @var string $method
     */
    public string $method;
    
    /**
     * @var string $path
     */
    public string $path;
    
    /**
     * @var callable|array $callback
     */
    public $callback;

    /**
     * @var array $options
     */
    public array $options;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param string $method
     * @param string $path
     * @param callable|array $callback
     * @param array $options
     */
    public function __construct(string $method, string $path, callable|array $callback, array $options)
    {
        $this->method = $method;
        $this->path = $path;
        $this->callback = $callback;
        $this->options = $options;
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
        if ($this->path == $path) {
            return new stdClass;
        }
        if (strpos($this->path, ':')) {
            $matches = [];
            $expression = '#(\\\\?\:([a-zA-Z0-9_]+))#';
            $pattern = preg_replace($expression, '(?<$2>[^/]+)', preg_quote($this->path), 9);
            preg_match_all("#^$pattern$#i", $path, $matches);
            if (($matches[0][0] ?? false) == $path) {
                $params = new stdClass();
                foreach($matches as $param => $match) {
                    if (is_numeric($param)) {
                        continue;
                    }
                    if (count($match) == 1) {
                        $params->$param = reset($match);
                    }
                    else if(count($match) > 1) {
                        $params->$param = $match;
                    }
                }
                return $params;
            }
        }
        return null;
    }

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
    public function execute(request $request, response $response, app $app, ?response $previous): ?response
    {
        return call_user_func_array($this->callback, [
            $request, $response, $app, $previous
        ]);
    }
}