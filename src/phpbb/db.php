<?php

namespace phpbb;

use phpbb\errors\ServerError;

class db
{
    private config $config;
    private string $connection;
    public array $schemas;

    public function __construct(config $config, ?string $connection = 'default')
    {
        $this->config = $config;
        $this->connection = $connection;
    }

    public function use($connection) {
        return new db($this->config, $connection);
    }

    public function collection(string $collection): db\collection
    {
        return new db\collection($this, $collection);
    }

    public function registerSchema(string $schema): db
    {
        $name = substr($schema, strrpos($schema, '\\') + 1);
        $this->schemas[$name] = $schema;
        return $this;
    }

    private static $cache = [];

    public function connector()
    {
        if (!isset(self::$cache[$this->connection])) {
            $config = $this->config->get('database')->get($this->connection);
            if (!$config) {
                throw new ServerError(sprintf(ServerError::NO_DATABASE_CONNECTION, $this->connection));
            }
            $class = sprintf('phpbb\\db\\connectors\\%s', $config->get('type'));
            if (!class_exists($class)) {
                throw new ServerError(sprintf(ServerError::DATABASE_NOT_SUPPORTED, $config->get('type')));
            }
            self::$cache[$this->connection] = new $class($config);
        }
        return self::$cache[$this->connection];
    }
}