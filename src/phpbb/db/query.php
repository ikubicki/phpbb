<?php

namespace phpbb\db;

use phpbb\db;
use phpbb\db\connectors\record;
use phpbb\db\connectors\records;

class query
{

    const SORT = 'sort';
    const LIMIT = 'limit';
    const SORT_ASCENDING = 'ascending';
    const SORT_DESCENDING = 'descending';
    const INSERT = 'insert';
    const AND = 'and';
    const OR = 'or';


    private db $db;
    public string $collection;
    public array $filters;
    public array $fields;
    public array $options;

    public function __construct(db $db, string $collection, array $filters, $options = [], array $fields = [])
    {
        $this->db = $db;
        $this->collection = $collection;
        $this->filters = $filters;
        $this->fields = $fields;
        $this->options = $options;
    }

    public function add($values): bool
    {
        return $this->db->connector()->add($this, $values);
    }

    public function addMany($values): bool
    {
        return $this->db->connector()->addMany($this, $values);
    }

    public function findOne(): ?record
    {
        $this->options[query::LIMIT] = 1;
        return $this->find($this)[0] ?? null;
    }

    public function find(): records
    {
        return $this->db->connector()->query($this);
    }

    public function update($values): bool
    {
        return $this->db->connector()->update($this, $values);
    }

    public function remove(): bool
    {
        return $this->db->connector()->remove($this);
    }

    public function transaction(): query
    {
        $this->db->connector()->transaction();
        return $this;
    }

    public function commit(): query
    {
        $this->db->connector()->commit();
        return $this;
    }

    public function rollback(): query
    {
        $this->db->connector()->rollback();
        return $this;
    }
}