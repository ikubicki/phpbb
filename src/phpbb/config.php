<?php

namespace phpbb;

use stdClass;

/**
 * Configuration loader class
 */
class config extends config\abstraction
{

    const LOG_LEVEL_DEBUG = 'debug';
    const LOG_LEVEL_INFO = 'info';
    const LOG_LEVEL_WARN = 'warn';
    const LOG_LEVEL_ERROR = 'error';
    const E_LEVELS = [
        self::LOG_LEVEL_DEBUG => E_ALL,
        self::LOG_LEVEL_INFO => E_PARSE | E_ERROR | E_WARNING | E_NOTICE,
        self::LOG_LEVEL_WARN => E_PARSE | E_ERROR | E_WARNING,
        self::LOG_LEVEL_ERROR => E_PARSE | E_ERROR,
    ];

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

    /**
     * Toggles debug mode
     * 
     * @author ikubicki
     * @param bool $enable
     * @return void
     */
    public static function logLevel($level): void
    {
        ini_set('display_errors', $level == self::LOG_LEVEL_DEBUG);
        ini_set('error_reporting', self::E_LEVELS[$level]);
    }

    /**
     * Returns environment variable value by name
     * 
     * @author ikubicki
     * @param string $name
     * @return string
     */
    public static function env(string $name): string
    {
        return getenv($name);
    }
}