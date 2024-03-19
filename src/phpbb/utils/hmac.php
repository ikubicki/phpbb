<?php

namespace phpbb\utils;

class hmac 
{
    public static function hash($data, $kid)
    {
        list($kid, $key) = keysManagement::getKey($kid);
        return 'v1.' . hash_hmac('sha256', $data, $key);
    }
}