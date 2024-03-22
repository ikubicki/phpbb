<?php

namespace phpbb\core\accessRules;

class threads extends resource
{
    const ACCESS_RULES = [
        'threads.view',
        'threads.edit',
        'threads.delete',
        'threads.create',
        'threads.post',
    ];
    const RESOURCE = 'threads';
}