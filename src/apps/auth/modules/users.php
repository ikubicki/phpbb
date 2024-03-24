<?php

namespace apps\auth\modules;

use phpbb\app;
use phpbb\apps\api\standardMethods;
use phpbb\core\accessRules\users as AccessRulesUsers;
use phpbb\errors\NotAuthorized;
use phpbb\errors\ResourceNotFound;
use phpbb\middleware\JwtAuthMiddleware;
use phpbb\middleware\permissionsMiddleware;
use phpbb\request;
use phpbb\response;

class users extends standardMethods
{
    const COLLECTION = 'users';

    public function setup()
    {
        $this->app->get('/users', [$this, 'getRecords'], [
            new jwtAuthMiddleware(),
            new permissionsMiddleware([AccessRulesUsers::VIEW]),
        ]);
        $this->app->post('/users', [$this, 'createRecord'], [
            new jwtAuthMiddleware(),
            new permissionsMiddleware([AccessRulesUsers::CREATE]),
        ]);
        $this->app->get('/users/:id', [$this, 'getRecord'], [
            new jwtAuthMiddleware(),
            new permissionsMiddleware([AccessRulesUsers::VIEW]),
        ]);
        $this->app->patch('/users/:id', [$this, 'patchRecord'], [
            new jwtAuthMiddleware(),
            new permissionsMiddleware([AccessRulesUsers::EDIT]),
        ]);
        $this->app->delete('/users/:id', [$this, 'deleteRecord'], [
            new jwtAuthMiddleware(),
            new permissionsMiddleware([AccessRulesUsers::DELETE]),
        ]);
        $this->app->get('/me', [$this, 'getMe'], [
            new jwtAuthMiddleware(),
        ]);
    }

    public function getMe(request $request, response $response, app $app)
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
        $response->send([
            'expires' => $auth->raw('exp', 0),
            'remaining' => $auth->raw('exp', 0) - time(),
            'user' => $user,
        ]);
    }
}