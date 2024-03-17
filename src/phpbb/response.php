<?php

namespace phpbb;

use phpbb\apps\router\error;
use phpbb\apps\router\route;
use Throwable;

class response
{

    const OK = 200;
    const NO_CONTENT = 204;
    const BAD_REQUEST = 400;
    const NOT_AUTHORIZED = 401;
    const NOT_FOUND = 404;
    const SERVER_ERROR = 500;

    public request $request;
    public route $route;
    public ?response $previous;
    public int $status = self::OK;
    public array $headers = [];
    public ?string $type;
    public $body;
    private $sent = false;

    public function __construct(request $request, route $route, ?response $previous = null)
    {
        $this->request = $request;
        $this->route = $route;
        $this->previous = $previous;
        $type = $this->extractType($request);
        if (!$type) {
            $type = 'application/json';
        }
        $this->type($type);
    }

    public function execute(app $app): response
    {
        try {
            $this->preExecution($app);
            $previous = $this->previous ? $this->previous->execute($app) : null;
            if (!$this->sent) {
                call_user_func_array($this->route->callback, [
                    $this->request, $this, $app, $previous
                ]);
            }
            $this->postExecution($app);
        }
        catch(Throwable $throwable) {
            call_user_func_array((new error($throwable))->callback, [
                $this->request, $this, $app, null
            ]);
        }
        return $this;
    }

    private function preExecution(app $app): void
    {
        $middleware = (array) ($this->route->options['preExecution'] ?? []);
        $this->executeMiddleware($app, $middleware);
    }

    private function postExecution(app $app): void
    {
        $middleware = (array) ($this->route->options['postExecution'] ?? []);
        $this->executeMiddleware($app, $middleware);
    }

    private function executeMiddleware(app $app, array $middleware): void
    {
        foreach($middleware as $callback) {
            if (!$this->sent) {
                call_user_func_array([$callback, 'execute'], [$this->request, $this, $app]);
            }
        }
    }

    public function status(int $status): response
    {
        $this->status = $status;
        return $this;
    }

    public function header($name, $value): response
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function send($body, ?string $type = null): response {
        $this->sent = true;
        $this->body = $body;
        if ($type) {
            $this->type($type);
        }
        return $this;
    }

    public function type(?string $type): response {
        $this->type = $type;
        return $this;
    }

    private function extractType(request $request)
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