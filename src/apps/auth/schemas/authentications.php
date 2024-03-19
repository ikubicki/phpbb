<?php

namespace apps\auth\schemas;

use phpbb\db\collection\entity;
use phpbb\db\collection\field;
use phpbb\utils\hmac;

class authentications extends entity
{
    public function __construct()
    {
        $this
            ->field('identifier')
            ->field('type', 'inactive', field::enum(['inactive', 'active', 'locked']))
            ->field('credential')
            ->field('settings', null, field::TYPE_OBJECT)
            ->index('type', field::INDEX_PRIMARY)
            ->index('identifier', field::INDEX_PRIMARY);
    }

    public function verify(string $credential)
    {
        return hmac::hash($credential, $this->kid) == $this->signature;
    }
}
