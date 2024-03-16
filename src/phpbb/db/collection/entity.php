<?php

namespace phpbb\db\collection;

use phpbb\db\collection;

class entity
{

    private collection $collection;
    private array $data = [];
    protected array $fields = [];
    protected array $references = [];
    protected array $indexes = [];

    public function collection(collection $collection)
    {
        $this->collection = $collection;
    }

    public function import($data): entity
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function __set($field, $value)
    {
        $this->data[$field] = isset($this->fields[$field]) ? $this->fields[$field]->process($value) : $value;
    }

    public function __get($field)
    {
        return $this->data[$field] ?? (isset($this->fields[$field]) ? $this->fields[$field]->default() : null);
    }

    public function export(): array
    {
        $data = (array) $this->data;
        unset($data['$id']);
        return $data;
    }

    protected function field(string $name, mixed $default = null, string $type = field::TYPE_STRING): entity
    {
        $this->fields[$name] = new field($name, $default, $type);
        $this->data[$name] = $this->fields[$name]->default();
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

    public function delete()
    {

    }

    public function save()
    {

    }
}