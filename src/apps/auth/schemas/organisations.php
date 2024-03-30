<?php

namespace apps\auth\schemas;

use phpbb\db\collection\entity;
use phpbb\db\collection\field;

/**
 * Organisations collection entity
 */
class organisations extends entity
{

    /**
     * The constructor
     * 
     * @author ikubicki
     */
    public function __construct()
    {
        $this
            ->field('uuid', null, field::TYPE_UUID, false, field::ON_CREATE)
            ->field('type', 'group', field::enum(['group', 'team', 'set']))
            ->field('name')
            ->field('description')
            ->field('default', false, field::TYPE_BOOLEAN)
            ->field('creator', null, field::TYPE_UUID)
            ->field('created', null, field::TYPE_UNIXTIME, false, field::ON_CREATE)
            ->field('modified', null, field::TYPE_UNIXTIME, false, field::ON_UPDATE)
            ->index('uuid', field::INDEX_PRIMARY)
            ->index('name', field::INDEX_PRIMARY)
            ->reference('creator', users::class, 'uuid');
    }

    /**
     * Sets metadata field value
     * 
     * @author ikubicki
     * @param string $field
     * @param mixed $value
     * @return organisations
     */
    public function metadata(string $field, mixed $value): organisations
    {
        $this->metadata->$field = $value;
        return $this;
    }
}
