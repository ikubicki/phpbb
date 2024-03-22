<?php

namespace apps\auth\modules;

use phpbb\app;
use phpbb\core\accessRules;
use phpbb\middleware\JwtAuthMiddleware;
use phpbb\request;
use phpbb\response;

class permissions
{

    private app $app;

    public function __construct(app $app)
    {
        $this->app = $app;
    }

    public function setup()
    {
        $options = [
            'preExecution' => [
                new JwtAuthMiddleware(),
            ]
        ];
        $this->app->get('/permissions', [$this, 'getPermissions'], $options);
        $this->app->post('/permissions', [$this, 'postPermissions'], $options);
    }

    public function getPermissions(request $request, response $response, app $app)
    {
        $auth = $request->context('auth');
        $accessRules = new accessRules();
        $accessRules->loadPermissions($app, $auth->raw('sub'));
        $response->send($accessRules);
    }

    public function postPermissions(request $request, response $response, app $app)
    {
        $auth = $request->context('auth');
        $accessRules = new accessRules();
        $accessRules->loadPermissions($app, $auth->raw('sub'));
        $response->send($accessRules->verify($request->body->toArray()));
    }
}