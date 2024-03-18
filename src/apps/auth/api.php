<?php

namespace apps\auth;

use phpbb\app;
use phpbb\middleware\JwtAuthMiddleware;

class api extends app
{

    private function setupSchemas()
    {
        $this->plugin('db')
            ->registerSchema(schemas\users::class)
            ->registerSchema(schemas\organisations::class);
    }

    private function setupRoutes()
    {

        $this->get('/me', require('modules/me.php'), [
            'preExecution' => [
                new JwtAuthMiddleware(),
            ]
        ]);
        $this->post('/authorize', require('modules/authorize.php'));
        (new modules\users($this))->setup();
        (new modules\organisations($this))->setup();
    }

    protected function setup($config): void
    {
        $this->setupSchemas();
        $this->setupRoutes();
    }
}