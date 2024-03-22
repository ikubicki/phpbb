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
        (new modules\authentications($this))->setup();
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