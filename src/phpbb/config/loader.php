<?php

namespace phpbb\config;

use RuntimeException;
use phpbb\config;
use phpbb\errors\ServerError;
use stdClass;

/**
 * Config loader class
 */
class loader
{

    /**
     * @var array $cache
     */
    private static array $cache = [];

    /**
     * Loads JSON contents from given file
     * 
     * @author ikubicki
     * @param string $file
     * @return ?stdClass
     */
    static public function load(string $file): ?stdClass
    {
        if (!isset(self::$cache[$file])) {
            if (!file_exists(config::$root . $file)) {
                throw new ServerError(sprintf(ServerError::CONFIG_MISSING, $file));
            }
            self::$cache[$file] = self::loadJsonFile(config::$root . $file);
        }
        return self::$cache[$file];
    }

    /**
     * 
     */
    private static function loadJsonFile(string $filename): ?stdClass
    {
        $data = json_decode(file_get_contents($filename));
        if (json_last_error()) {
            throw new ServerError(sprintf(ServerError::CONFIG_INVALID_JSON, basename($filename)));
        }
        return $data;
    }
}