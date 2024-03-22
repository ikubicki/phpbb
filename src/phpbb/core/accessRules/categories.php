<?php

namespace phpbb\core\accessRules;

class categories extends resource
{
    const ACCESS_RULES = [
        'categories.view',
        'categories.edit',
        'categories.delete',
        'categories.create',
        'categories.post',
    ];
    const RESOURCE = 'categories';

}