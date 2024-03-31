<?php

namespace apps\auth;

use phpbb\app;
use phpbb\config;
use phpbb\request;
use phpbb\response;

class api extends app
{

    /**
     * Registers application schemas into database handler
     * 
     * @author ikubicki
     * @return void
     */
    private function setupSchemas(): void
    {
        $this->plugin('db')
            ->registerSchema(schemas\authentications::class)
            ->registerSchema(schemas\memberships::class)
            ->registerSchema(schemas\organisations::class)
            ->registerSchema(schemas\policies::class)
            ->registerSchema(schemas\users::class);
    }

    /**
     * Setups application routes
     * 
     * @author ikubicki
     * @return void
     */
    private function setupRoutes(): void
    {
        $this->get('/', [$this, 'getIndex']);
        (new modules\authentications($this))->setup();
        (new modules\organisations($this))->setup();
        (new modules\permissions($this))->setup();
        (new modules\users($this))->setup();
    }

    /**
     * Setups the application
     * 
     * @author ikubicki
     * @param config $config
     * @return void
     */
    protected function setup(config $config): void
    {
        $this->setupSchemas();
        $this->setupRoutes();
    }

    /**
     * Returns index page
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @return response
     */
    public function getIndex(request $request, response $response): response
    {
        return $response->file(__DIR__ . '/resources/index.html');
    }
}