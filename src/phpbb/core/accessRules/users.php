<?php

namespace phpbb\core\accessRules;

class users extends resource
{
    const ACCESS_RULES = [
        'users.view',
        'users.edit',
        'users.delete',
        'users.create',
        'users.permissions',
    ];
    const RESOURCE = 'users';
}