<?php

namespace phpbb\db\collection;

use DateTime;
use DateTimeInterface;
use phpbb\db\errors\FieldError;
use Ramsey\Uuid\Uuid;

class field
{
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_TIME = 'time';
    const TYPE_UNIXTIME = 'unixtime';
    const TYPE_UUID = 'uuid';
    const TYPE_OBJECT = 'object';
    const TYPE_ARRAY = 'array';

    const INDEX_FIELD = 0;
    const INDEX_UNIQUE = 1;
    const INDEX_PRIMARY = 2;

    const ON_CREATE = 1;
    const ON_ADD = 2;
    const ON_UPDATE = 3;
    const ON_DELETE = 4;

    private string $name;
    private string $type;
    private mixed $default; 
    private bool $writable = true;
    private ?int $behaviour = null;

    public function __construct(
        string $name, 
        mixed $default, 
        string $type = self::TYPE_STRING, 
        bool $writable = true,
        int $behaviour = null
    )
    {
        $this->name = $name;
        $this->default = $default;
        $this->type = $type;
        $this->writable = $writable;
        $this->behaviour = $behaviour;
        if ($behaviour == self::ON_CREATE) {
            $this->default = $this->calculateValue();
        }
    }

    public function __toString()
    {
        return $this->name;
    }

    public function default()
    {
        return $this->default;
    }

    public function process($value = false)
    {
        if ($value === false) {
            return $this->default;
        }
        if (!$this->writable) {
            throw new FieldError(sprintf(FieldError::NOT_WRITABLE, $this->name));
        }
        switch($this->type) {
            case self::TYPE_UUID: return (string) $value;
            case self::TYPE_STRING: return (string) $value;
            case self::TYPE_UNIXTIME:
            case self::TYPE_INTEGER: return (int) $value;
            case self::TYPE_FLOAT: return (float) $value;
            case self::TYPE_OBJECT: return (object) $value;
            case self::TYPE_ARRAY: return array_values((array) $value);
            case self::TYPE_DATE: 
            case self::TYPE_DATETIME: 
            case self::TYPE_TIME: 
                if (!$value instanceof DateTimeInterface) {
                    return new DateTime($value);
                }
                return $value;
            default:
                throw new FieldError(sprintf(FieldError::UNDEFINED_DATA_TYPE, $this->type));
        }
    }

    public function getBehaviour(): int
    {
        return $this->behaviour;
    }

    public function calculateValue(): mixed
    {
        switch($this->type) {
            default:
                return $this->default;
            case self::TYPE_UUID:
                return (string) Uuid::uuid4();
            case self::TYPE_DATETIME:
            case self::TYPE_DATE:
            case self::TYPE_TIME:
                return new DateTime();
            case self::TYPE_UNIXTIME:
                return time();
        }
    }
}