<?php

namespace phpbb\core\accessRules;

class users extends resource
{
    const VIEW = 'users.view';
    const EDIT = 'users.edit';
    const DELETE = 'users.delete';
    const CREATE = 'users.create';
    const PERMISSIONS = 'users.permissions';
    const ACCESS_RULES = [
        self::VIEW,
        self::EDIT,
        self::DELETE,
        self::CREATE,
        self::PERMISSIONS,
    ];
    const RESOURCE = 'users';
}