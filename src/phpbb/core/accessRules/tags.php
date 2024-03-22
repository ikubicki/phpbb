<?php

namespace phpbb\core\accessRules;

class tags extends resource
{
    const ACCESS_RULES = [
        'tags.view',
        'tags.edit',
        'tags.delete',
        'tags.create',
    ];
    const RESOURCE = 'tags';
}