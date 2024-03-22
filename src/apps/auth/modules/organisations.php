<?php

namespace apps\auth\modules;

use phpbb\apps\api\standardMethods;
use phpbb\middleware\JwtAuthMiddleware;

class organisations extends standardMethods
{

    const COLLECTION = 'organisations';

    public function setup()
    {
        $options = [
            'preExecution' => [
                new JwtAuthMiddleware(),
            ]
        ];
        $this->app->get('/' . self::COLLECTION, [$this, 'getRecords'], $options);
        $this->app->post('/' . self::COLLECTION, [$this, 'createRecord'], $options);
        $this->app->get('/' . self::COLLECTION . '/:id', [$this, 'getRecord'], $options);
        $this->app->patch('/' . self::COLLECTION . '/:id', [$this, 'patchRecord'], $options);
        $this->app->delete('/' . self::COLLECTION . '/:id', [$this, 'deleteRecord'], $options);
    }
}