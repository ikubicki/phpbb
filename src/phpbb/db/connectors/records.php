<?php

namespace phpbb\db\connectors;

use ArrayIterator;
use JsonSerializable;

/**
 * Databases records collection class
 */
class records extends ArrayIterator implements JsonSerializable
{
    
    /**
     * Returns an array
     * 
     * @author ikubicki
     * @return array
     */
    public function toArray(): array
    {
        return (array) $this;
    }

    /**
     * Hydrates records into entities
     * 
     * @author ikubicki
     * @param callable $hydrator
     * @return ArrayIterator
     */
    public function hydrate(callable $hydrator): ArrayIterator
    {
        foreach($this as $i => $record) {
            $this[$i] = $record->hydrate($hydrator);
        }
        return $this;
    }

    /**
     * JSON serializer
     * 
     * @author ikubicki
     * @return array
     */
    public function jsonSerialize(): array
    {
        return (array) $this;
    }
}