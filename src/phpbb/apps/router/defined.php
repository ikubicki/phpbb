<?php

namespace phpbb\apps\router;

class defined implements route
{
    public string $method;
    public string $path;
    public $callback;
    public array $options;

    public function __construct(string $method, string $path, callable|array $callback, array $options)
    {
        $this->method = $method;
        $this->path = $path;
        $this->callback = $callback;
        $this->options = $options;
    }

    public function test($path)
    {
        if ($this->path == $path) {
            return [];
        }
        if (strpos($this->path, ':')) {
            $matches = [];
            $expression = '#(\\\\?\:([a-zA-Z0-9_]+))#';
            $pattern = preg_replace($expression, '(?<$2>[^/]+)', preg_quote($this->path), 9);
            @preg_match_all("#^$pattern$#i", $path, $matches);
            if (($matches[0][0] ?? false) == $path) {
                $params = [];
                foreach($matches as $param => $match) {
                    if (is_numeric($param)) {
                        continue;
                    }
                    if (count($match) == 1) {
                        $params[$param] = reset($match);
                    }
                    else if(count($match) > 1) {
                        $params[$param] = $match;
                    }
                }
                return $params;
            }
        }
        return false;
    }
}