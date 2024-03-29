<?php

namespace phpbb;

use phpbb\config;
use phpbb\request;
use phpbb\response;
use phpbb\apps\router;
use phpbb\errors\ServerError;

/**
 * Application abstraction class
 */
abstract class app 
{

    /**
     * @var config\abstraction $config
     */
    private config\abstraction $config;

    /**
     * @var router $router
     */
    private router $router;

    /**
     * @var request $request
     */
    private request $request;

    /**
     * @var array $plugins
     */
    private array $plugins = [];

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param config\abstraction $config
     */
    public function __construct(config\abstraction $config)
    {
        $this->config = $config;
        $this->router = new router($config);
    }

    /**
     * Registers GET route
     * 
     * @author ikubicki
     * @param string $path
     * @param callable|array $handler
     * @param array $options
     * @return void
     */
    function get(string $path, callable|array $handler, array $options = []): void
    {
        $this->router->register(
            $this->router->route($this->router::GET, $path, $handler, $options)
        );
    }

    /**
     * Registers POST route
     * 
     * @author ikubicki
     * @param string $path
     * @param callable|array $handler
     * @param array $options
     * @return void
     */
    function post(string $path, callable|array $handler, array $options = [])
    {
        $this->router->register(
            $this->router->route($this->router::POST, $path, $handler, $options)
        );
    }

    /**
     * Registers PUT route
     * 
     * @author ikubicki
     * @param string $path
     * @param callable|array $handler
     * @param array $options
     * @return void
     */
    function put(string $path, callable|array $handler, array $options = [])
    {
        $this->router->register(
            $this->router->route($this->router::PUT, $path, $handler, $options)
        );
    }

    /**
     * Registers PATCH route
     * 
     * @author ikubicki
     * @param string $path
     * @param callable|array $handler
     * @param array $options
     * @return void
     */
    function patch(string $path, callable|array $handler, array $options = [])
    {
        $this->router->register(
            $this->router->route($this->router::PATCH, $path, $handler, $options)
        );
    }

    /**
     * Registers DELETE route
     * 
     * @author ikubicki
     * @param string $path
     * @param callable|array $handler
     * @param array $options
     * @return void
     */
    function delete(string $path, callable|array $handler, array $options = [])
    {
        $this->router->register(
            $this->router->route($this->router::DELETE, $path, $handler, $options)
        );
    }

    /**
     * Request handler
     * 
     * @author ikubicki
     * @param request $request
     * @return response
     */
    public function handle(request $request): response
    {
        $this->setup($this->config);
        $routes = $this->router->find($request);
        $response = null;
        foreach($routes as $route) {
            $response = new response($request, $route, $response);
        }
        $this->request = $request;
        return $response->execute($this);
    }

    /**
     * Stores a plugin for the application
     * 
     * @author ikubicki
     * @param string $name
     * @param mixed $instance
     * @return void
     */
    public function addPlugin(string $name, mixed $instance): void
    {
        $this->plugins[$name] = $instance;
    }

    /**
     * Returns a plugin for the application by given name
     * Throws ServerError if plugin is not registered
     * 
     * @author ikubicki
     * @param string $name
     * @return mixed
     * @throws ServerError
     */
    public function plugin(string $name): mixed
    {
        if (!isset($this->plugins[$name])) {
            throw new ServerError("Plugin $name not available");
        }
        return $this->plugins[$name];
    }

    /**
     * Generates the URL for given path
     * 
     * @author ikubicki
     * @param string $uri
     * @return string
     */
    public function url(string $uri, array $query = []): string
    {
        if (count($query)) {
            $uri .= (strpos($uri, '?') ? '&' : '?') . http_build_query($query);
        }
        return $this->request->http->base . '/' . ltrim($uri, '/');
    }

    /**
     * Application setup method
     * A place to register routes and database schemas
     * 
     * @author ikubicki
     * @param config $config
     */
    abstract protected function setup(config $config): void;
}