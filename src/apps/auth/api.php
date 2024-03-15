<?php

namespace apps\auth;

use phpbb\app;
use phpbb\middleware\JwtAuthMiddleware;

class api extends app
{

    protected function setup($config)
    {
        $this->get('/me', require('modules/me.php'), [
            'preExecution' => [
                new JwtAuthMiddleware(),
            ]
        ]);
        $this->post('/authenticate', require('modules/authenticate.php'));
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