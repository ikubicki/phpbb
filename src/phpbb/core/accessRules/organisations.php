<?php

namespace phpbb\core\accessRules;

class organisations extends resource
{
    const ACCESS_RULES = [
        'organisations.view',
        'organisations.edit',
        'organisations.delete',
        'organisations.create',
        'organisations.extend',
        'organisations.permissions',
    ];
    const RESOURCE = 'organisations';
}