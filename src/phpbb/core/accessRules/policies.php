<?php

namespace phpbb\core\accessRules;

class policies extends resource
{
    const VIEW = 'policies.view';
    const EDIT = 'policies.edit';
    const DELETE = 'policies.delete';
    const CREATE = 'policies.create';
    const ACCESS_RULES = [
        self::VIEW,
        self::EDIT,
        self::DELETE,
        self::CREATE,
    ];
    const RESOURCE = 'policies';
}