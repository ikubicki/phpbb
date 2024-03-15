<?php

namespace phpbb\core\event;

class bus
{

    private string $source;
    private static array $events = [];

    public function __construct(string $source)
    {
        $this->source = $source;
        if (!isset(self::$events[$this->source])) {
            self::$events[$this->source] = [];
        }
    }

    public function on(string $event, callable $callback)
    {
        self::$events[$this->source][$event] = $callback;
    }

    public function emit(string $event, array $args = [])
    {
        if (isset(self::$events[$this->source][$event])) {
            call_user_func_array(self::$events[$this->source][$event], $args);
        }
    }
}