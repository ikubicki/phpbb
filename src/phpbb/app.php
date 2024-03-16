<?php

namespace phpbb;

use phpbb\config;
use phpbb\request;
use phpbb\response;
use phpbb\apps\router;
use phpbb\errors\ServerError;

abstract class app 
{

    private config\abstraction $config;
    private router $router;
    private array $plugins = [];

    public function __construct(config\abstraction $config)
    {
        $this->config = $config;
        $this->router = new router($config);
    }

    function get(string $path, callable|array $handler, array $options = []) 
    {
        $this->router->register(
            $this->router->route($this->router::GET, $path, $handler, $options)
        );
    }

    function post(string $path, callable|array $handler, array $options = [])
    {
        $this->router->register(
            $this->router->route($this->router::POST, $path, $handler, $options)
        );
    }

    function put(string $path, callable|array $handler, array $options = [])
    {
        $this->router->register(
            $this->router->route($this->router::PUT, $path, $handler, $options)
        );
    }

    function delete(string $path, callable|array $handler, array $options = [])
    {
        $this->router->register(
            $this->router->route($this->router::DELETE, $path, $handler, $options)
        );
    }

    public function handle(request $request): response
    {
        $this->setup($this->config);
        return $this->router->find($request)->execute($this);
    }

    public function addPlugin($name, $instance)
    {
        $this->plugins[$name] = $instance;
    }

    public function plugin($name)
    {
        if (!isset($this->plugins[$name])) {
            throw new ServerError("Plugin $name not available");
        }
        return $this->plugins[$name];
    }

    public function url($uri)
    {
        return $this->getBaseUrl() . '/' . ltrim($uri, '/');
    }

    protected function getBaseUrl()
    {
        $protocol = $this->config->get('protocol', 'http');
        $hostname = $this->config->get('hostname', 'localhost');
        $port = $this->config->get('port', false);
        $path = $this->config->get('path', 'path');
        
        if ($port == 80) {
            $protocol = 'http';
        }
        if ($port == 443) {
            $protocol = 'https';
        }
        return sprintf(
            '%s://%s%s%s', 
            $protocol,
            $hostname, 
            $port ? ":$port" : '',
            $path ? '/' . trim($path, '/') : $path
        );
    }

    abstract public function setup(config $config);
}