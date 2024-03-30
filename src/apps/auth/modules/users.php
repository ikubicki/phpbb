<?php

namespace apps\auth\modules;

use apps\auth\middleware\jwtAuthMiddleware;
use apps\auth\middleware\permissionsMiddleware;
use phpbb\app;
use phpbb\apps\api\standardMethods;
use phpbb\core\accessRules\policies;
use phpbb\core\accessRules\users as AccessRulesUsers;
use phpbb\errors\NotAuthorized;
use phpbb\errors\ResourceNotFound;
use phpbb\request;
use phpbb\response;

class users extends standardMethods
{
    const COLLECTION = 'users';

    /**
     * Setups application routes
     * 
     * @author ikubicki
     * @return void
     */
    public function setup()
    {
        $this->app->get('/users', [$this, 'getRecords'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesUsers::VIEW]),
            ]
        ]);
        $this->app->post('/users', [$this, 'createRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesUsers::VIEW]),
            ]
        ]);
        $this->app->get('/users/:id', [$this, 'getRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesUsers::VIEW]),
            ]
        ]);
        $this->app->get('/users/:id/permissions', [$this, 'getRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([policies::VIEW]),
            ]
        ]);
        $this->app->patch('/users/:id/permissions', [$this, 'getRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([policies::EDIT]),
            ]
        ]);
        $this->app->patch('/users/:id', [$this, 'patchRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesUsers::VIEW]),
            ]
        ]);
        $this->app->delete('/users/:id', [$this, 'deleteRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesUsers::VIEW]),
            ]
        ]);
        $this->app->get('/me', [$this, 'getMe'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
            ]
        ]);
    }

    /**
     * Handles GET /me request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws NotAuthorized
     * @throws ResourceNotFound
     */
    public function getMe(request $request, response $response, app $app): response
    {
        $auth = $request->context('auth');
        $subject = $auth->raw('sub');
        if (!$subject) {
            throw new NotAuthorized($request);
        }
        $user = $app
            ->plugin('db')
            ->collection('users')
            ->findOne(['uuid' => $subject]);
        if (!$user) {
            throw new ResourceNotFound($request);
        }
        return $response->send([
            'expires' => $auth->raw('exp', 0),
            'remaining' => $auth->raw('exp', 0) - time(),
            'user' => $user,
        ]);
    }
}