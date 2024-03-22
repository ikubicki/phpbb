<?php

namespace apps\auth\modules;

use phpbb\apps\api\standardMethods;

class users extends standardMethods
{
    const COLLECTION = 'users';

    public function setup()
    {
        $this->app->get('/' . self::COLLECTION, [$this, 'getRecords']);
        $this->app->post('/' . self::COLLECTION, [$this, 'createRecord']);
        $this->app->get('/' . self::COLLECTION . '/:id', [$this, 'getRecord']);
        $this->app->patch('/' . self::COLLECTION . '/:id', [$this, 'patchRecord']);
        $this->app->delete('/' . self::COLLECTION . '/:id', [$this, 'deleteRecord']);
    }
}