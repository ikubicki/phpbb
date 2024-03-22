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
            ->registerSchema(schemas\organisations::class)
            ->registerSchema(schemas\authentications::class);
    }

    private function setupRoutes()
    {

        $this->get('/me', require('modules/me.php'), [
            'preExecution' => [
                new JwtAuthMiddleware(),
            ]
        ]);

        $this->post('/authorize', require('modules/authorize.php'));
        
        (new modules\organisations($this))->setup();
        (new modules\permissions($this))->setup();
        (new modules\users($this))->setup();
    }

    protected function setup($config): void
    {
        $this->setupSchemas();
        $this->setupRoutes();
    }
}