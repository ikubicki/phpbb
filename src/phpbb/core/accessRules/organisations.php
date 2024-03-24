<?php

namespace phpbb\core\accessRules;

class organisations extends resource
{
    const VIEW = 'organisations.view';
    const EDIT = 'organisations.edit';
    const DELETE = 'organisations.delete';
    const CREATE = 'organisations.create';
    const EXTEND = 'organisations.extend';
    const PERMISSIONS = 'organisations.permissions';
    const ACCESS_RULES = [
        self::VIEW,
        self::EDIT,
        self::DELETE,
        self::CREATE,
        self::EXTEND,
        self::PERMISSIONS,
    ];
    const RESOURCE = 'organisations';
}