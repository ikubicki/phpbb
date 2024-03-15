<?php

namespace phpbb\config;

use RuntimeException;

class loader
{
    static $cache = [];
    static public function load(string $file) {
        static $root = __DIR__ . '/../../';
        if (!isset(self::$cache[$file])) {
            self::$cache[$file] = json_decode(file_get_contents($root . $file));
            if (json_last_error()) {
                throw new RuntimeException(
                    "Unable to load $file configuration: " . json_last_error_msg()
                );
            }
        }
        return self::$cache[$file];
    }
}