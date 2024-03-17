<?php

namespace phpbb\db;

use phpbb\db;
use phpbb\db\query;
use phpbb\db\collection\entity;
use phpbb\db\connectors\records;

/**
 * Database collection class
 */
class collection
{
    /**
     * @var db $db
     */
    public db $db;

    /**
     * @var string $collection
     */
    private string $collection;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param db $db
     * @param string $collection
     */
    public function __construct(db $db, string $collection)
    {
        $this->db = $db;
        $this->collection = $collection;
    }

    /**
     * Creates a new entity
     * 
     * @author ikubicki
     * @return entity
     */
    public function create(): entity
    {
        $entity = null;
        if ($this->db->schemas[$this->collection] ?? false) {
            $class = $this->db->schemas[$this->collection];
            $entity = new $class;
        }
        if (!$entity) {
            $entity = new entity;
        }
        $entity->collection($this);
        return $entity;
    }

    /**
     * Queries for multiple collection entities
     * 
     * @author ikubicki
     * @param ?array $filters
     * @param array $options
     * @param array $fields
     * @return records
     */
    public function find(?array $filters = [], array $options = [], array $fields = []): records
    {
        return $this
            ->query($filters, $options, $fields)
            ->find()
            ->hydrate($this->getHydrator());
    }

    /**
     * Queries for a single collection entity
     * 
     * @author ikubicki
     * @param ?array $filters
     * @param array $options
     * @param array $fields
     * @return ?entity
     */
    public function findOne(?array $filters, ?array $options = [], ?array $fields = []): ?entity
    {
        $record = $this
            ->query($filters, $options, $fields)
            ->findOne();
        if ($record) {
            return $record->hydrate($this->getHydrator());
        }
        return $record;
    }

    /**
     * Adds values as new document to database
     * 
     * @author ikubicki
     * @param array $values
     * @param array $options
     * @return bool
     */
    public function add(array $values, array $options = []): bool
    {
        return $this
            ->query([], $options)
            ->add($values);
    }

    /**
     * Updates documents in the database
     * 
     * @author ikubicki
     * @param array $filters
     * @param array $values
     * @param array $options
     * @return bool
     */
    public function update(array $filters, array $values, array $options = []): bool
    {
        return $this
            ->query($filters, $options)
            ->update($values);
    }

    /**
     * Removes documents from the database
     * 
     * @author ikubicki
     * @param array $filters
     * @param array $options
     * @return bool
     */
    public function remove(array $filters, array $options = []): bool
    {
        return $this
            ->query($filters, $options)
            ->remove();
    }

    /**
     * Returns a query object that allows to execute commands on database
     * 
     * @author ikubicki
     * @param ?array $filters
     * @param array $options
     * @param array $fields
     * @return query
     */
    public function query(?array $filters, array $options = [], array $fields = []): query
    {
        return new query($this->db, $this->collection, $filters ?: [], $options ?: [], $fields ?: []);
    }

    /**
     * Returns a hydration callable
     * 
     * @author ikubicki
     * @return callable
     */
    private function getHydrator(): callable
    {
        $class = $this->db->schemas[$this->collection] ?? false;
        $collection = $this;
        // records iterator
        return function($records) use ($class, $collection) {

            // actual hydrating function
            $hydrator = function($record) use ($class, $collection) {
                if ($class) {
                    $entity = new $class();
                    $entity->import((array) $record);
                    $entity->collection($collection);
                    return $entity;
                }
                return $record;
            };

            if (is_array($records)) {
                foreach($records as $i => $record) {
                    $records[$i] = call_user_func($hydrator, $record);
                }
                return $records;
            }
            return call_user_func($hydrator, $records);
        };
    }
}