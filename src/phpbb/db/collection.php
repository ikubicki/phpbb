<?php

namespace phpbb\db;

use phpbb\db;
use phpbb\db\collection\entity;

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

    public function find(?array $query, ?array $options = [], ?array $fields = [])
    {
        return $this
            ->query($query, $options, $fields)
            ->hydrate($this->getHydrator());
    }

    public function findOne(?array $query, ?array $options = [], ?array $fields = [])
    {
        $options[query::LIMIT] = 1;
        return $this
            ->query($query, $options, $fields)
            ->hydrate($this->getHydrator());
    }

    public function query(?array $query, ?array $options = [], ?array $fields = []): db\query
    {
        return new db\query($this->db, $this->collection, $query ?: [], $options ?: [], $fields ?: []);
    }

    private function getHydrator()
    {
        $class = $this->db->schemas[$this->collection] ?? false;
        return function($records) use ($class) {

            $hydrator = function($record) use ($class) {
                if ($class) {
                    $entity = new $class();
                    $entity->import((array) $record);
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