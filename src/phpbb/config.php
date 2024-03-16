<?php

namespace phpbb;

class config extends config\abstraction
{
    public static $root;
    protected $data;
    public function __construct($file) {
        $this->data = config\loader::load($file);
    }

    public static function root($path)
    {
        self::$root = rtrim($path, '/') . '/';
    }
}