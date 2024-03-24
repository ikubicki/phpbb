<?php

namespace phpbb\core\accessRules;

class tags extends resource
{
    const VIEW = 'tags.view';
    const EDIT = 'tags.edit';
    const DELETE = 'tags.delete';
    const CREATE = 'tags.create';
    const ACCESS_RULES = [
        self::VIEW,
        self::EDIT,
        self::DELETE,
        self::CREATE,
    ];
    const RESOURCE = 'tags';
}