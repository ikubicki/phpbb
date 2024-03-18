<?php

namespace phpbb;

use phpbb\db\errors\DatabaseError;

/**
 * Database handler class
 */
class db
{

    /**
     * @var config\abstraction $config
     */
    private config\abstraction $config;

    /**
     * @var string $connection
     */
    private string $connection;

    /**
     * @var array $schemas
     */
    private static array $schemas = [];

    /**
     * @var array $cache
     */
    private static array $cache = [];

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param config\abstraction $config
     * @param string $connection
     */
    public function __construct(config\abstraction $config, string $connection = 'default')
    {
        $this->config = $config;
        $this->connection = $connection;
        if (!isset(self::$schemas[$this->connection])) {
            self::$schemas[$this->connection] = [];
        }
    }

    /**
     * Switches connection
     * Returns a new instance
     * 
     * @author ikubicki
     * @param string $connection
     * @return db
     */
    public function use(string $connection): db
    {
        return new db($this->config, $connection);
    }

    /**
     * Returns a database collection handler
     * 
     * @author ikubicki
     * @param string $collection
     * @return db\collection
     */
    public function collection(string $collection): db\collection
    {
        return new db\collection($this, $collection);
    }

    /**
     * Registers new collection schema
     * 
     * @author ikubicki
     * @param string $class
     * @return db
     */
    public function registerSchema(string $class): db
    {
        $collection = substr($class, strrpos($class, '\\') + 1);
        self::$schemas[$this->connection][$collection] = $class;
        return $this;
    }

    /**
     * Returns registered collection schema
     * 
     * @author ikubicki
     * @param string $collection
     * @return ?string
     */
    public function getSchema(string $collection): ?string
    {
        return self::$schemas[$this->connection][$collection] ?? null;
    }

    /**
     * Returns an instance of database connector
     * Throws DatabaseError if connection is misconfigured
     * 
     * @author ikubicki
     * @return db\connectors\abstraction
     * @throws DatabaseError
     */
    public function connector(): db\connectors\abstraction
    {
        if (!isset(self::$cache[$this->connection])) {
            $config = $this->config->get('database')->get($this->connection);
            if (!$config) {
                throw new DatabaseError(sprintf(DatabaseError::NO_DATABASE_CONNECTION, $this->connection));
            }
            $class = sprintf('phpbb\\db\\connectors\\%s', $config->get('type'));
            if (!class_exists($class)) {
                throw new DatabaseError(sprintf(DatabaseError::DATABASE_NOT_SUPPORTED, $config->get('type')));
            }
            self::$cache[$this->connection] = new $class($config);
        }
        return self::$cache[$this->connection];
    }
}