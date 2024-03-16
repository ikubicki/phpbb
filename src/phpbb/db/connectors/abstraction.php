<?php

namespace phpbb\db\connectors;

use phpbb\config;
use phpbb\db\query;
use stdClass;

abstract class abstraction
{
    protected config\item $config;

    public function __construct(config\item $config)
    {
        $this->config = $config;
    }

    abstract protected function connection();
    abstract public function add(query $query, array $values): ?stdClass;
    abstract public function update(query $query, array $values): array;
    abstract public function query(query $query): array;
    abstract public function remove(query $query): bool;
}