<?php

namespace apps\auth\schemas;

use phpbb\db\collection\entity;
use phpbb\db\collection\field;
use stdClass;

/**
 * Users collection entity
 */
class users extends entity
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
            ->field('name')
            ->field('status', 'inactive', field::enum(['inactive', 'active', 'locked']))
            ->field('metadata', new stdClass, field::TYPE_OBJECT)
            ->field('created', null, field::TYPE_UNIXTIME, false, field::ON_CREATE)
            ->field('modified', null, field::TYPE_UNIXTIME, false, field::ON_UPDATE)
            ->index('uuid', field::INDEX_PRIMARY);
    }

    /**
     * Sets metadata field value
     * 
     * @author ikubicki
     * @param string $field
     * @param mixed $value
     * @return users
     */
    public function metadata(string $field, mixed $value): users
    {
        $this->metadata->$field = $value;
        return $this;
    }

    /**
     * Sets avatar URL
     * 
     * @author ikubicki
     * @param string $url
     * @return users
     */
    public function avatar(string $url): users
    {
        $this->metadata('avatar', (object) [
            'url' => $url
        ]);
        return $this;
    }
}
