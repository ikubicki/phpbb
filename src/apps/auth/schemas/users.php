<?php

namespace apps\auth\schemas;

use phpbb\db\collection\entity;
use phpbb\db\collection\field;
use stdClass;

class users extends entity
{
    public function __construct()
    {
        $this
            ->field('uuid', null, field::TYPE_UUID, false, field::ON_CREATE)
            ->field('name')
            ->field('status', 'inactive', field::enum(['inactive', 'active', 'locked']))
            ->field('metadata', new stdClass, field::TYPE_OBJECT)
            ->field('created', null, field::TYPE_UNIXTIME, false, field::ON_CREATE)
            ->field('modified', null, field::TYPE_UNIXTIME, false, field::ON_UPDATE)
            ->index('uuid', field::INDEX_PRIMARY);
    }

    public function metadata($field, $value)
    {
        $this->metadata->$field = $value;
    }

    public function avatar($url)
    {
        $this->metadata('avatar', (object) [
            'url' => $url
        ]);
    }
}
