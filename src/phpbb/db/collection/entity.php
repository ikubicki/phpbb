<?php

namespace phpbb\db\collection;

use JsonSerializable;
use phpbb\db;
use phpbb\db\collection;
use phpbb\db\collection\field\enum;
use phpbb\db\collection\field\selection;
use phpbb\db\errors\ValidationError;

/**
 * Entity class
 */
class entity implements JsonSerializable
{

    const NEW = -1;
    const DELETED = 0;
    const MODIFIED = 1;
    const CURRENT = 2;
    
    /**
     * @var array $data
     */
    private array $data = [];

    /**
     * @var collection $collection
     */
    public collection $collection;

    /**
     * @var db $db
     */
    protected db $db;

    /**
     * @var array $fields
     */
    protected array $fields = [];

    /**
     * @var array $references
     */
    protected array $references = [];

    /**
     * @var array $indexes
     */
    protected array $indexes = [];

    /**
     * @var array $validators
     */
    protected array $validators = [];

    /**
     * @var array $hide
     */
    protected array $hide = ['$id'];

    /**
     * @var array $hooks
     */
    protected array $hooks = [];

    /**
     * @var int $__status
     */
    private int $__status = self::NEW;

    /**
     * Sets collection instance
     * 
     * @author ikubicki
     * @param collection $collection
     * @return void
     */
    public function collection(collection $collection): void
    {
        $this->collection = $collection;
        $this->db = $collection->db;
    }

    /**
     * Imports entity data
     * 
     * @author ikubicki
     * @param array $data
     * @return entity
     */
    public function import(array $data): entity
    {
        $this->data = array_merge($this->data, $data);
        $this->__status = self::CURRENT;
        return $this;
    }

    /**
     * Magic setter allows to set entity values
     * Marks entity as modified
     * 
     * @author ikubicki
     * @param string $field
     * @param mixed $value
     */
    public function __set(string $field, mixed $value)
    {
        if ($this->validators[$field] ?? false) {
            $this->validateField($field, $this->validators[$field], $value);
        }
        // schema defined entity
        if (count($this->fields) && isset($this->fields[$field])) {
            $this->data[$field] = $this->fields[$field]->process($value);
            $this->status(self::MODIFIED);
        }
        // freeform entity
        else if (!count($this->fields)) {
            $this->data[$field] = $value;
            $this->status(self::MODIFIED);
        }
    }

    /**
     * Magic getter allows to get entity values
     * 
     * @author ikubicki
     * @param string $field
     * @return mixed
     */
    public function __get($field): mixed
    {
        // return stored value
        if (isset($this->data[$field])) {
            return $this->data[$field];
        }
        // return defined field default value
        if (isset($this->fields[$field])) {
            return $this->fields[$field]->default();
        }
        return null;
    }

    /**
     * Allows to set multiple fields for given values collection
     * 
     * @author ikubicki
     * @param array $fields
     * @return entity
     */
    public function setMany(array $fields): entity
    {
        foreach($fields as $field => $value) {
            $this->__set($field, $value);
        }
        return $this;
    }

    /**
     * Exports entity values
     * 
     * @author ikubicki
     * @return array
     */
    public function export(): array
    {
        $data = (array) $this->data;
        unset($data['$id']);
        $data = array_filter(
            $data,
            [$this, 'filterHiddenFields'],
            ARRAY_FILTER_USE_KEY
        );

        return $data;
    }

    private function filterHiddenFields($field)
    {
        return !in_array($field, $this->hide);
    }

    /**
     * JSON serialization method
     * 
     * @author ikubicki
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->export();
    }

    /**
     * Returns a database handler
     * 
     * @author ikubicki
     * @return db
     */
    public function db(): db
    {
        return $this->collection->db;
    }

    /**
     * Adds field definition
     * 
     * @author ikubicki
     * @param string $name
     * @param mixed $default
     * @param string|enum|selection $type
     * @param bool $writable
     * @param int $behavior
     * @return static
     */
    protected function field(
        string $name, 
        mixed $default = null, 
        string|enum|selection $type = field::TYPE_STRING, 
        bool $writable = true,
        int $behavior = null,
    ): static
    {
        $this->fields[$name] = new field($name, $default, $type, $writable, $behavior);
        $this->data[$name] = $this->fields[$name]->default();
        if ($behavior) {
            $this->hooks[$behavior][$name] = $this->fields[$name];
        }
        return $this;
    }

    /**
     * Adds reference definition
     * 
     * @author ikubicki
     * @param string $field
     * @param string $class
     * @param string $referencedField
     * @return static
     */
    protected function reference(string $field, string $class, string $referencedField): static
    {
        $collection = substr($class, strrpos($class, '\\') + 1);
        $this->references[$field] = new reference($field, $collection, $referencedField);
        return $this;
    }

    /**
     * Returns a collection of registered references
     * 
     * @author ikubicki
     * @return array
     */
    public function getReferences(): array
    {
        return $this->references;
    }

    /**
     * Returns referenced entity by field name
     * 
     * @author ikubicki
     * @param string $field
     * @return ?entity
     */
    public function getReferencedEntity(string $field): ?entity
    {
        if (empty($this->references[$field])) {
            return null;
        }
        if (!$this->__get($field)) {
            return $this->__get($field);
        }
        return $this->references[$field]->getEntity($this);
    }

    /**
     * Adds index definition
     * 
     * @author ikubicki
     * @param string $field
     * @param string $type
     * @return static
     */
    protected function index(string $field, string $type = field::INDEX_FIELD): static
    {
        $this->indexes[$field] = $type;
        return $this;
    }

    /**
     * Adds hidden fields flag
     * 
     * @author ikubicki
     * @param array $fields
     * @return static
     */
    protected function hide(array $fields): static 
    {
        $this->hide = [...$this->hide, ...$fields];
        return $this;
    }

    /**
     * Adds field validation definition
     * 
     * @author ikubicki
     * @param string $field
     * @param string $type
     * @return static
     */
    protected function validate(string $field, string $type, mixed $value): static
    {
        $this->validators[$field][$type] = $value;
        return $this;
    }

    /**
     * Sets entity status
     * Returns calculated status
     * 
     * @author ikubicki
     * @param int $status
     * @return int
     */
    private function status(int $status): int
    {
        return $this->__status = $this->__status < $status ? $this->__status : $status;
    }

    /**
     * Deletes an entity from database collection
     * Marks entity as deleted
     * 
     * @author ikubicki
     * @return entity
     */
    public function delete(): entity
    {
        if ($this->__status > self::DELETED) {
            $this->callHooks(field::ON_DELETE);
            $this->collection->remove($this->getFilters());
            $this->status(self::DELETED);
        }
        return $this;
    }

    /**
     * Saves entity to database collection
     * Calls registered field behaviour hooks
     * Marks entity as current
     * 
     * @author ikubicki
     * @return entity
     */
    public function save(): entity
    {
        if ($this->__status === self::NEW) {
            $this->callHooks(field::ON_ADD);
            $this->collection->add($this->export());
            $this->status(self::CURRENT);
        }
        else if ($this->__status < self::CURRENT) {
            $this->callHooks(field::ON_UPDATE);
            $this->collection->update($this->getFilters(), $this->getValues());
            $this->status(self::CURRENT);
        }
        return $this;
    }

    /**
     * Calls field behaviour hooks
     * 
     * @author ikubicki
     * @param int $behaviour
     * @return void
     */
    private function callHooks(int $behaviour): void
    {
        foreach(($this->hooks[$behaviour] ?? []) as $field => $definition) {
            $this->data[$field] = $definition->calculateValue();
        }
    }

    /**
     * Returns primary type indexes
     * 
     * @author ikubicki
     * @return array
     */
    protected function pks(): array
    {
        return array_keys($this->indexes, field::INDEX_PRIMARY);
    }

    /**
     * Returns query filters
     * Returns values of primary indexes
     * 
     * @author ikubicki
     * @return array
     */
    protected function getFilters(): array
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

    /**
     * Returns query values
     * Returns values of non primary index fields
     * 
     * @author ikubicki
     * @return array
     */
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

    protected function validateField(string $field, array $validators, mixed $value): void
    {
        foreach($validators as $validator => $expected)
        {
            switch($validator) {
                case field::VALIDATE_LENGTH:
                    if (strlen(trim($value)) < $expected) {
                        throw new ValidationError(
                            sprintf(ValidationError::LENGTH, $field, $expected)
                        );
                    }
                    break;
            }
        }
    }
}