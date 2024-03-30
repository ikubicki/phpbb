<?php

namespace phpbb;

use phpbb\apps\router\error;
use phpbb\apps\router\route;
use phpbb\errors\ServerError;
use Throwable;

class response
{

    const OK = 200;
    const NO_CONTENT = 204;
    const FOUND = 302;
    const REDIRECT = 302;
    const BAD_REQUEST = 400;
    const NOT_AUTHORIZED = 401;
    const NOT_FOUND = 404;
    const SERVER_ERROR = 500;

    /**
     * @var app $app
     */
    private app $app;

    /**
     * @var request $request
     */
    public request $request;

    /**
     * @var route $route
     */
    public route $route;

    /**
     * @var ?response $previous
     */
    public ?response $previous;

    /**
     * @var int $status
     */
    public int $status = self::OK;

    /**
     * @var array $headers
     */
    public array $headers = [];

    /**
     * @var array $cookies
     */
    public array $cookies = [];

    /**
     * @var ?string $type
     */
    public ?string $type;

    /**
     * @var mixed $body
     */
    public mixed $body;

    /**
     * @var bool $sent
     */
    private bool $sent = false;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param app $app
     * @param request $request
     * @param route $route
     * @param ?response $previous
     */
    public function __construct(app $app, request $request, route $route, ?response $previous = null)
    {
        $this->app = $app;
        $this->request = $request;
        $this->route = $route;
        $this->previous = $previous;
        $type = $this->extractType($request);
        if (!$type) {
            $type = 'application/json';
        }
        $this->type($type);
    }

    /**
     * Executes application handler for route
     * Executes error route handler on error
     * 
     * @author ikubicki
     * @param app $app
     * @return response
     */
    public function execute(app $app): response
    {
        $previous = null;
        try {
            $this->preExecution($app);
            $previous = $this->previous ? $this->previous->execute($app) : null;
            if (!$this->sent) {
                $this->route->execute($this->request, $this, $app, $previous);
            }
            $this->postExecution($app);
        }
        catch(Throwable $throwable) {
            (new error($throwable))->execute($this->request, $this, $app, $previous);
        }
        return $this;
    }

    /**
     * Calls pre execution middleware
     * 
     * @author ikubicki
     * @param app $app
     * @return void
     */
    private function preExecution(app $app): void
    {
        $middlewares = (array) ($this->route->options['preExecution'] ?? []);
        $this->executeMiddleware($app, $middlewares);
    }

    /**
     * Calls post execution middlewares
     * 
     * @author ikubicki
     * @param app $app
     * @return void
     */
    private function postExecution(app $app): void
    {
        $middlewares = (array) ($this->route->options['postExecution'] ?? []);
        $this->executeMiddleware($app, $middlewares);
    }

    /**
     * Calls middleware
     * 
     * @author ikubicki
     * @param app $app
     * @param array $middlewares
     * @return void
     */
    private function executeMiddleware(app $app, array $middlewares): void
    {
        foreach($middlewares as $middleware) {
            if (!$this->sent) {
                call_user_func_array([$middleware, 'execute'], [$this->request, $this, $app]);
            }
        }
    }

    /**
     * Loads a file into response body and sets the content type
     * 
     * @author ikubicki
     * @param string $filename
     * @param string $type
     * @return response
     */
    public function file(string $filename, string $type = null): response
    {
        if (!file_exists($filename)) {
            throw new ServerError(sprintf('File doesn\'t exists: %s', basename($filename)));
        }
        if (!$type) {
            $type = mime_content_type($filename);
        }
        return $this->type($type)->send(file_get_contents($filename));
    }

    /**
     * Sets response status
     * 
     * @author ikubicki
     * @param int $status
     * @return response
     */
    public function status(int $status): response
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Sets response header
     * 
     * @author ikubicki
     * @param string $name
     * @param string $value
     * @return response
     */
    public function header(string $name, string $value): response
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Sets response body and flags as sent
     * Optionally sets the type
     * 
     * @author ikubicki
     * @param mixed $body
     * @param ?string $type
     * @return response
     */
    public function send(mixed $body, ?string $type = null): response
    {
        $this->sent = true;
        $this->body = $body;
        if ($type) {
            $this->type($type);
        }
        return $this;
    }

    /**
     * Sets the type of response
     * 
     * @author ikubicki
     * @param ?string $type
     * @return response
     */
    public function type(?string $type): response
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Sets redirection headers and content
     * 
     * @author ikubicki
     * @param string $url
     * @return response
     */
    public function redirect(string $url): response
    {
        return $this->header('Location', $url)
            ->type('text/html')
            ->status(self::REDIRECT)
            ->send(sprintf('<meta http-equiv="refresh" content="0; url=%s" />', $url));
    }

    /**
     * Sets cookie
     * 
     * @author ikubicki
     * @param string $name
     * @param string $value
     * @param array $options
     * @return response
     */
    public function cookie(string $name, string $value, array $options = []): response
    {
        $cookieOptions = $this->app->config
            ->get('cookie')
            ->get('options');

        if (!array_key_exists('domain', $options)) {
            $options['domain'] = $cookieOptions->raw('domain', $this->request->url->hostname);
        }
        if (!array_key_exists('path', $options)) {
            $options['path'] = $cookieOptions->raw('path', $this->request->url->path);
        }
        if (!array_key_exists('secure', $options)) {
            $options['secure'] = $cookieOptions->raw('secure', $this->request->http->ssl);
        }
        if (!array_key_exists('httponly', $options)) {
            $options['httponly'] = $cookieOptions->raw('httponly', true);
        }
        if (!array_key_exists('samesite', $options)) {
            $options['samesite'] = $cookieOptions->raw('samesite', true);
        }
        $this->cookies[$name] = [
            'value' => $value,
            'options' => $options,
        ];
        return $this;
    }

    /**
     * Extracts the type from request accept header
     * 
     * @author ikubicki
     * @param request $request
     * @return ?string
     */
    private function extractType(request $request): ?string
    {
        if (!$request->accept) {
            return null;
        }
        if(stripos($request->accept, 'application/json') !== false) {
            return 'application/json';
        }
        if(stripos($request->accept, 'application/xml') !== false) {
            return 'application/xml';
        }
        return null;
    }
}