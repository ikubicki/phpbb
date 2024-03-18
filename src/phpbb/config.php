<?php

namespace phpbb;

use stdClass;

/**
 * Configuration loader class
 */
class config extends config\abstraction
{
    /**
     * @var string $root
     */
    public static string $root;

    /**
     * @var ?stdClass $data
     */
    protected ?stdClass $data;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param string $file
     */
    public function __construct(string $file)
    {
        $this->data = config\loader::load($file);
    }

    /**
     * Sets or returns (if parameter is omitted) root path
     * 
     * @author ikubicki
     * @param ?string $path
     * @return ?string
     */
    public static function root(?string $path = null): ?string
    {
        if ($path) {
            self::$root = rtrim($path, '/') . '/';
        }
        return self::$root;
    }
}