<?php

namespace apps\auth\schemas;

use phpbb\db\collection\entity;
use phpbb\db\collection\field;

/**
 * Organisations memberships collection entity
 */
class memberships extends entity
{

    /**
     * The constructor
     * 
     * @author ikubicki
     */
    public function __construct()
    {
        $this
            ->field('member')
            ->field('organisations', [], field::TYPE_ARRAY)
            ->index('member', field::INDEX_PRIMARY);
    }
}
