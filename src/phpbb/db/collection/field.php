<?php

namespace phpbb\db\collection;

use DateTime;
use DateTimeInterface;
use phpbb\db\collection\field\enum;
use phpbb\db\collection\field\selection;
use phpbb\db\errors\FieldError;
use Ramsey\Uuid\Uuid;

/**
 * Field definition
 */
class field
{
    const TYPE_BOOLEAN = 'bool';
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

    const VALIDATE_LENGTH = 1;

    /**
     * @var string $name
     */
    private string $name;

    /**
     * @var string|enum|selection $type
     */
    private string|enum|selection $type;

    /**
     * @var mixed $default
     */
    private mixed $default; 

    /**
     * @var bool $writable
     */
    private bool $writable = true;

    /**
     * @var ?int $behavior
     */
    private ?int $behavior = null;

    /**
     * The constructor
     * Calls ON_CREATE behaviour hooks
     * 
     * @author ikubicki
     * @param string $name
     * @param mixed $default
     * @param string|object type
     * @param bool $writable
     * @param int $behavior
     */
    public function __construct(
        string $name, 
        mixed $default, 
        string|enum|selection $type = self::TYPE_STRING, 
        bool $writable = true,
        int $behavior = null
    )
    {
        $this->name = $name;
        $this->default = $default;
        $this->type = $type;
        $this->writable = $writable;
        $this->behavior = $behavior;
        if ($behavior == self::ON_CREATE) {
            $this->default = $this->calculateValue();
        }
    }

    /**
     * Returns new enum instance
     * 
     * @author ikubicki
     * @param array $options
     * @param ?string $default
     * @return enum
     */
    public static function enum(array $options, ?string $default = null): enum
    {
        return new enum($options, $default);
    }

    /**
     * Returns new selection instance
     * 
     * @author ikubicki
     * @param array $options
     * @param ?string $default
     * @return selection
     */
    public static function selection(array $options, ?string $default = null): selection
    {
        return new selection($options, $default);
    }

    /**
     * String serialized
     * 
     * @author ikubicki
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * Returns default value
     * 
     * @author ikubicki
     * @return mixed
     */
    public function default(): mixed
    {
        return $this->default;
    }

    /**
     * Field value processor
     * Casts value to field type
     * 
     * @author ikubicki
     * @param mixed $value
     * @return mixed
     */
    public function process(mixed $value = false): mixed
    {
        if ($this->type instanceof enum) {
            return $this->type->process($value);
        }
        if ($this->type instanceof selection) {
            return $this->type->process($value);
        }
        if ($value === false) {
            return $this->default;
        }
        if (!$this->writable) {
            throw new FieldError(sprintf(FieldError::NOT_WRITABLE, $this->name));
        }
        switch($this->type) {
            case self::TYPE_BOOLEAN: return $value == 'true' || $value == 1;
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

    /**
     * Calculates value for field
     * 
     * @author ikubicki
     * @return mixed
     */
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