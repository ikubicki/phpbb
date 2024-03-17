<?php

namespace phpbb\db\connectors;

use stdClass;

/**
 * Database record class
 */
class record
{

    /**
     * @var stdClass $data
     */
    public stdClass $data;

    /**
     * The constructor 
     * 
     * @author ikubicki
     * @param stdClass $data
     */
    public function __construct(stdClass $data)
    {
        $this->data = $data;
    }

    /**
     * Hydrates the object into an entity
     * 
     * @author ikubicki
     * @param callable $hydrator
     * @return mixed
     */
    public function hydrate(callable $hydrator): mixed
    {
        return call_user_func($hydrator, $this->data);
    }
}