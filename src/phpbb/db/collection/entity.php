<?php

namespace phpbb\db\collection;

use JsonSerializable;
use phpbb\db\collection;

class entity implements JsonSerializable
{

    const NEW = -1;
    const DELETED = 0;
    const MODIFIED = 1;
    const CURRENT = 2;

    private collection $collection;
    private array $data = [];
    protected array $fields = [];
    protected array $references = [];
    protected array $indexes = [];
    protected array $hooks = [];
    private int $__status = self::NEW;

    public function collection(collection $collection): void
    {
        $this->collection = $collection;
    }

    public function import($data): entity
    {
        $this->data = array_merge($this->data, $data);
        $this->__status = self::CURRENT;
        return $this;
    }

    public function __set($field, $value)
    {
        $this->data[$field] = isset($this->fields[$field]) ? $this->fields[$field]->process($value) : $value;
        $this->status(self::MODIFIED);
    }

    public function __get($field)
    {
        return $this->data[$field] ?? (isset($this->fields[$field]) ? $this->fields[$field]->default() : null);
    }

    public function setMany(array $fields): entity
    {
        foreach($fields as $field => $value) {
            $this->__set($field, $value);
        }
        return $this;
    }

    public function export(): array
    {
        $data = (array) $this->data;
        unset($data['$id']);
        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->export();
    }

    protected function field(
        string $name, 
        mixed $default = null, 
        string $type = field::TYPE_STRING, 
        bool $writable = true,
        int $behaviour = null,
    ): entity
    {
        $this->fields[$name] = new field($name, $default, $type, $writable, $behaviour);
        $this->data[$name] = $this->fields[$name]->default();
        if ($behaviour) {
            $this->hooks[$behaviour][$name] = $this->fields[$name];
        }
        return $this;
    }

    protected function reference(string $field, string $entity): entity
    {
        $this->references[$field] = $entity;
        return $this;
    }

    protected function index(string $field, string $type = field::INDEX_FIELD): entity
    {
        $this->indexes[$field] = $type;
        return $this;
    }

    private function status(int $status): int
    {
        return $this->__status = $this->__status < $status ? $this->__status : $status;
    }

    public function delete(): entity
    {
        if ($this->__status > self::DELETED) {
            $this->callHooks(field::ON_DELETE);
            $this->collection->remove($this->getQuery());
            $this->status(self::DELETED);
        }
        return $this;
    }

    public function save(): entity
    {
        if ($this->__status === self::NEW) {
            $this->callHooks(field::ON_ADD);
            $this->collection->add($this->export());
            $this->status(self::CURRENT);
        }
        else if ($this->__status < self::CURRENT) {
            $this->callHooks(field::ON_UPDATE);
            $this->collection->update($this->getQuery(), $this->getValues());
            $this->status(self::CURRENT);
        }
        return $this;
    }

    private function callHooks(int $hook)
    {
        foreach(($this->hooks[$hook] ?? []) as $field => $definition) {
            $this->data[$field] = $definition->calculateValue();
        }
    }

    protected function pks(): array
    {
        return array_keys($this->indexes, field::INDEX_PRIMARY);
    }

    protected function getQuery(): array
    {
        $indexes = $this->pks();
        return array_filter(
            $this->export(),
            function ($key) use ($indexes) {
                return in_array($key, $indexes);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    protected function getValues(): array
    {
        $indexes = $this->pks();
        return array_filter(
            $this->export(),
            function ($key) use ($indexes) {
                return !in_array($key, $indexes);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}