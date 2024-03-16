<?php

namespace phpbb\db;

use phpbb\db;
use phpbb\errors\ServerError;

class query
{

    const SORT = 'sort';
    const LIMIT = 'limit';
    const SORT_ASCENDING = 'ascending';
    const SORT_DESCENDING = 'descending';
    const AND = 'and';
    const OR = 'or';


    private db $db;
    public string $collection;
    public array $query;
    public array $fields;
    public array $options;

    public function __construct(db $db, string $collection, array $query, $options = [], array $fields = [])
    {
        $this->db = $db;
        $this->collection = $collection;
        $this->query = $query;
        $this->fields = $fields;
        $this->options = $options;
    }

    public function insertOne($values)
    {
        return $this->db->connector()->add($this, $values);
    }

    public function insert($values)
    {
        $results = [];
        foreach($values as $_values) {
            if (!is_array($_values)) {
                throw new ServerError(ServerError::PROVIDE_INSERT_COLLECTION);
            }
            $results[] = $this->db->connector()->add($this, $_values);
        }
        return $results;
    }

    public function findOne()
    {
        $this->options[query::LIMIT] = 1;
        return $this->find($this)[0] ?? [];
    }

    public function find()
    {
        return $this->db->connector()->query($this);
    }

    public function update($values)
    {
        return $this->db->connector()->modify($this, $values);
    }

    public function delete()
    {
        return $this->db->connector()->remove($this);
    }

    public function hydrate(callable $hydrator)
    {
        $callable = [$this, 'find'];
        if (($this->options[query::LIMIT] ?? 0) == 1) {
            $callable = [$this, 'findOne'];
        }
        return call_user_func($hydrator, $callable());
    }
}