<?php 

namespace apps\auth\modules\authentications;

class factory
{

    /**
     * Produces authentication handler object or null on failure
     * 
     * @author ikubicki
     * @param string $type
     * @param array $args
     * @return ?abstraction
     */
    public static function produce(string $type, array $args = []): ?abstraction
    {
        $class = __NAMESPACE__ . "\\$type";
        if (class_exists($class)) {
            return new $class(...$args);
        }
        return null;
    }
}