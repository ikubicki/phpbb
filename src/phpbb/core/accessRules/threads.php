<?php

namespace phpbb\core\accessRules;

class threads extends resource
{
    const VIEW = 'threads.view';
    const EDIT = 'threads.edit';
    const DELETE = 'threads.delete';
    const CREATE = 'threads.create';
    const POST = 'threads.post';
    const PERMISSIONS = 'threads.permissions';
    const ACCESS_RULES = [
        self::VIEW,
        self::EDIT,
        self::DELETE,
        self::CREATE,
        self::POST,
        self::PERMISSIONS,
    ];
    const RESOURCE = 'threads';
}