<?php

namespace phpbb\apps;

use phpbb\apps\router\error;
use phpbb\apps\router\route;
use phpbb\response;
use phpbb\errors\NotFound;
use phpbb\request;

class router
{
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';
    const OPTIONS = 'OPTIONS';

    static private array $table = [];

    /**
     * Instantiates route definition
     * 
     * @author ikubicki
     * @param string $method
     * @param string $path
     * @param callable|array $callback
     * @param array $options
     * @return route
     */
    public function route(string $method, string $path, callable|array $callback, array $options): route
    {
        return new router\defined($method, $path, $callback, $options);
    }

    /**
     * Registers new route
     * 
     * @author ikubicki
     * @param route $route
     * @return router
     */
    public function register(route $route): router
    {
        if (empty(self::$table[$route->method])) {
            self::$table[$route->method] = [];
        }
        self::$table[$route->method][] = $route;
        return $this;
    }

    /**
     * Finds matching routes for given request
     * 
     * @author ikubicki
     * @param request $request
     * @return array
     */
    public function find(request $request): array
    {
        $routes = [];
        foreach((self::$table[$request->method] ?? []) as $route) {
            $check = $route->test($request->http->path);
            if ($check !== false) {
                $request->uri->import($check);
                $routes[] = $route;
            }
        }
        if (!count($routes)) {
            $routes[] = new error(new NotFound($request));
        }
        return $routes;
    }
}