<?php

namespace phpbb\db\collection\field;

class selection
{

    /**
     * @var array $options
     */
    private array $options;

    /**
     * @var string $default
     */
    private string $default;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param array $options
     * @param ?string $default
     */
    public function __construct(array $options, ?string $default = null)
    {
        $this->options = $options;
        $this->default = $default ?? reset($options);
    }

    /**
     * Enum value processor
     * 
     * @author ikubicki
     * @param ?string $value
     * @return string
     */
    public function process(?string $value): string
    {
        return in_array($value, $this->options) ? $value : $this->default;
    }
}