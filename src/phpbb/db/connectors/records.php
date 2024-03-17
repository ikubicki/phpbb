<?php

namespace phpbb\db\connectors;

use ArrayIterator;
use JsonSerializable;

class records extends ArrayIterator implements JsonSerializable
{
    public function hydrate(callable $hydrator)
    {
        foreach($this as $i => $record) {
            $this[$i] = $record->hydrate($hydrator);
        }
        return $this;
    }

    public function jsonSerialize(): array
    {
        return (array) $this;
    }
}