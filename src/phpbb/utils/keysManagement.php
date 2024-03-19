<?php

namespace phpbb\utils;

use phpbb\config;

/**
 * Keys management class
 */
class keysManagement
{

    /**
     * @var array $keysStore
     */
    private static array $keysStore = [];
    
    /**
     * Returns config instance
     * 
     * @author ikubicki
     * @return config
     */
    private static function getConfig(): config
    {
        static $config;
        if (!$config) {
            $config = new config('config/keys.json');
        }
        return $config;
    }

    /**
     * Returns keys collection
     * 
     * @author ikubicki
     * @return array
     */
    private static function getKeyStore(): array
    {
        if (!self::$keysStore) {
            self::$keysStore = (array) self::getConfig()->raw('keys');
        }
        return self::$keysStore;
    }

    /**
     * Returns a key ID and the key
     * 
     * @author ikubicki
     * @param string $kid
     * @return array
     */
    public static function getKey(string $kid = null): array
    {
        $keysStore = self::getKeyStore();
        if (!$kid) {
            $kid = array_rand($keysStore);
        }
        return [$kid, $keysStore[$kid]];
    }
}