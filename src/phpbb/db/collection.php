<?php

namespace phpbb\db;

use phpbb\db;
use phpbb\db\collection\entity;
use phpbb\db\connectors\records;

class collection
{
    private db $db;
    private string $collection;

    public function __construct(db $db, string $collection)
    {
        $this->db = $db;
        $this->collection = $collection;
    }

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

    public function find(?array $query, ?array $options = [], ?array $fields = []): records
    {
        return $this
            ->query($query, $options, $fields)
            ->find()
            ->hydrate($this->getHydrator());
    }

    public function findOne(?array $query, ?array $options = [], ?array $fields = []): ?entity
    {
        $record = $this
            ->query($query, $options, $fields)
            ->findOne();
        if ($record) {
            return $record->hydrate($this->getHydrator());
        }
        return $record;
    }

    public function query(?array $query, ?array $options = [], ?array $fields = []): db\query
    {
        return new db\query($this->db, $this->collection, $query ?: [], $options ?: [], $fields ?: []);
    }

    public function add(?array $values, ?array $options = []): bool
    {
        return $this
            ->query([], $options)
            ->add($values);
    }

    public function update(?array $query, ?array $values, ?array $options = []): bool
    {
        return $this
            ->query($query, $options)
            ->update($values);
    }

    public function remove(?array $query, ?array $options = []): bool
    {
        return $this
            ->query($query, $options)
            ->remove();
    }

    private function getHydrator()
    {
        $class = $this->db->schemas[$this->collection] ?? false;
        $collection = $this;
        return function($records) use ($class, $collection) {

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