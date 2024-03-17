<?php

namespace apps\auth;

use phpbb\app;
use phpbb\middleware\JwtAuthMiddleware;

class api extends app
{

    public function setup($config)
    {
        $this->plugin('db')
            ->registerSchema(schemas\users::class)
            ->registerSchema(schemas\organisations::class);

        $this->get('/me', require('modules/me.php'), [
            'preExecution' => [
                new JwtAuthMiddleware(),
            ]
        ]);
        $this->post('/authorize', require('modules/authorize.php'));
        $this->get('/users', [modules\users::class, 'getUsers']);
        $this->post('/users', [modules\users::class, 'createUser']);
        $this->get('/users/:userId', [modules\users::class, 'getUser']);
        $this->patch('/users/:userId', [modules\users::class, 'patchUser']);
        $this->delete('/users/:userId', [modules\users::class, 'deleteUser']);

        //$this->post('/login/:param', [$this, 'loginParam']);
        //$this->post('/login/:parama/:paramb', [$this, 'loginParam']);
    }

    public function loginParam($request, $response)
    {
        $response->send([
            'call' => 'loginParam',
            'params' => $request->params,
        ]);
    }
}