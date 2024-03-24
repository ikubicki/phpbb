<?php

namespace phpbb\core\accessRules;

class categories extends resource
{
    const VIEW = 'categories.view';
    const EDIT = 'categories.edit';
    const DELETE = 'categories.delete';
    const CREATE = 'categories.create';
    const POST = 'categories.post';
    const PERMISSIONS = 'categories.permissions';
    const ACCESS_RULES = [
        self::VIEW,
        self::EDIT,
        self::DELETE,
        self::CREATE,
        self::POST,
        self::PERMISSIONS,
    ];
    const RESOURCE = 'categories';

}