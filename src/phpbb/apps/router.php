<?php

namespace phpbb\apps;


use phpbb\response;
use phpbb\core\event;
use phpbb\errors\NotFound;

class router
{
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';
    const OPTIONS = 'OPTIONS';

    private event\bus $eventBus;
    static private array $table = [];

    public function __construct()
    {
        $this->eventBus = new event\bus('router');
    }

    public function route(string $method, string $path, callable|array $callback, array $options)
    {
        return new router\defined($method, $path, $callback, $options);
    }

    public function register(router\route $route)
    {
        if (empty(self::$table[$route->method])) {
            self::$table[$route->method] = [];
        }
        self::$table[$route->method][] = $route;
    }

    public function find($request)
    {
        $response = null;
        foreach((self::$table[$request->method] ?? []) as $route) {
            $params = $route->test($request->http->path);
            if ($params !== false) {
                $request->uri->import($params);
                $response = new response($request, $route, $response);
            }
        }
        if (!$response) {
            $response = new response($request, new router\error(
                new NotFound($request)
            ));
        }
        return $response;
    }
}