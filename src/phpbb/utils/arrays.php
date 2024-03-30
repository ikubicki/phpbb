<?php

namespace phpbb\utils;

class arrays 
{

    public static function extractUuids(array $collection)
    {
        return array_map(fn($item) => $item->uuid, $collection);
    }
}