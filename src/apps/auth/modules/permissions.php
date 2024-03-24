<?php

namespace apps\auth\modules;

use phpbb\app;
use phpbb\core\accessRules;
use phpbb\core\accessRules\policies;
use phpbb\middleware\JwtAuthMiddleware;
use phpbb\middleware\permissionsMiddleware;
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
        $this->app->get('/permissions', [$this, 'getPermissions'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([
                    policies::VIEW,
                ])
            ]
        ]);
        $this->app->post('/permissions', [$this, 'postPermissions'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([
                    policies::VIEW,
                ])
            ]
        ]);
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
        $response->send($accessRules->getRules($request->body->toArray()));
    }
}