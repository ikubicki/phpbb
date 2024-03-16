<?php

namespace phpbb\config;

use RuntimeException;
use phpbb\config;

class loader
{
    static $cache = [];
    static public function load(string $file) {
        if (!isset(self::$cache[$file])) {
            self::$cache[$file] = json_decode(file_get_contents(config::$root . $file));
            if (json_last_error()) {
                throw new RuntimeException(
                    "Unable to load $file configuration: " . json_last_error_msg()
                );
            }
        }
        return self::$cache[$file];
    }
}