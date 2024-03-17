<?php

namespace phpbb\db\connectors;

use phpbb\db\connectors\records;
use phpbb\config;
use phpbb\db\query;

/**
 * Connector abstraction
 */
abstract class abstraction
{
    /**
     * @var config\item $config
     */
    protected config\item $config;

    /**
     * Object constructor
     * 
     * @author ikubicki
     * @param config\item $config
     */
    public function __construct(config\item $config)
    {
        $this->config = $config;
    }

    /**
     * Starts the transaction
     * 
     * @author ikubicki
     * @return abstraction
     */
    abstract public function transaction(): abstraction;

    /**
     * Commits the transaction
     * 
     * @author ikubicki
     * @return abstraction
     */
    abstract public function commit(): abstraction;

    /**
     * Rollbacks the transaction
     * 
     * @author ikubicki
     * @return abstraction
     */
    abstract public function rollback(): abstraction;

    /**
     * Inserts a single document for a given query and values
     * 
     * @author ikubicki
     * @param query $query
     * @param array $values
     * @return bool
     */
    abstract public function add(query $query, array $values): bool;

    /**
     * Inserts multiple documents for a given query and collection of values
     * 
     * @author ikubicki
     * @param query $query
     * @param array $collection
     * @return bool
     */
    abstract public function addMany(query $query, array $collection): bool;

    /**
     * Updates documents for a given query with provided values
     * 
     * @author ikubicki
     * @param query $query
     * @param array $values
     * @return bool
     */
    abstract public function update(query $query, array $values): bool;

    /**
     * Removes documents for a query
     * 
     * @author ikubicki
     * @param query $query
     * @return bool
     */
    abstract public function remove(query $query): bool;
    
    /**
     * Executes a find command for a query
     * Returns an iterable collection with hydration ability
     * 
     * @author ikubicki
     * @param query $query
     * @return records
     */
    abstract public function query(query $query): records;
}