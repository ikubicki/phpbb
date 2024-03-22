<?php

namespace apps\auth\modules;

use phpbb\app;
use phpbb\apps\api\standardMethods;
use phpbb\errors\BadRequest;
use phpbb\errors\NotAuthorized;
use phpbb\errors\ResourceNotFound;
use phpbb\middleware\JwtAuthMiddleware;
use phpbb\request;
use phpbb\response;

class users extends standardMethods
{
    const COLLECTION = 'users';

    public function setup()
    {
        $options = [
            'preExecution' => [
                new JwtAuthMiddleware(),
            ]
        ];
        $this->app->get('/' . self::COLLECTION, [$this, 'getRecords'], $options);
        $this->app->post('/' . self::COLLECTION, [$this, 'createRecord'], $options);
        $this->app->get('/' . self::COLLECTION . '/:id', [$this, 'getRecord'], $options);
        $this->app->patch('/' . self::COLLECTION . '/:id', [$this, 'patchRecord'], $options);
        $this->app->delete('/' . self::COLLECTION . '/:id', [$this, 'deleteRecord'], $options);
        $this->app->get('/me', [$this, 'getMe'], $options);
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