<?php

namespace apps\auth\modules;

use apps\auth\modules\authentications\factory;
use phpbb\app;
use phpbb\apps\api\standardMethods;
use phpbb\core\accessRules\users;
use phpbb\errors\BadRequest;
use phpbb\middleware\JwtAuthMiddleware;
use phpbb\middleware\permissionsMiddleware;
use phpbb\request;
use phpbb\response;

class authentications extends standardMethods
{
    const COLLECTION = 'authentications';

    public function setup()
    {
        $this->app->get('/' . self::COLLECTION, [$this, 'getRecords'], [
            'preExecution' => [
                new JwtAuthMiddleware(),
                new permissionsMiddleware([
                    users::CREATE
                ])
            ]
        ]);
        $this->app->post('/' . self::COLLECTION, [$this, 'createRecord'], [
            'preExecution' => [
                new JwtAuthMiddleware(),
                new permissionsMiddleware([
                    users::EDIT
                ])
            ]
        ]);
        $this->app->get('/' . self::COLLECTION . '/:id', [$this, 'getRecord'], [
            'preExecution' => [
                new JwtAuthMiddleware(),
                new permissionsMiddleware([
                    users::VIEW
                ])
            ]
        ]);
        $this->app->patch('/' . self::COLLECTION . '/:id', [$this, 'patchRecord'], [
            'preExecution' => [
                new JwtAuthMiddleware(),
                new permissionsMiddleware([
                    users::EDIT
                ])
            ]
        ]);
        $this->app->delete('/' . self::COLLECTION . '/:id', [$this, 'deleteRecord'], [
            'preExecution' => [
                new JwtAuthMiddleware(),
                new permissionsMiddleware([
                    users::EDIT
                ])
            ]
        ]);
        $this->app->post('/authorize', [$this, 'postAuthorize']);
        $this->app->get('/authorize/oauth', [$this, 'getAuthorizeOauth']);
    }

    public function postAuthorize(request $request, response $response, app $app)
    {
        $identifier = $request->body->raw('identifier') ?: $request->query('identifier');
        $type = $request->body->raw('type') ?: $request->query('type');
        $handler = factory::produce($type, [$request, $response, $app]);
        if ($handler) {
            return $handler->execute($identifier);
        }
        throw new BadRequest(sprintf('Invalid type of %s', $request->query('type')));
    }


    public function getAuthorizeOauth(request $request, response $response, app $app)
    {
        $identifier = $request->body->raw('identifier') ?: $request->query('identifier');
        $handler = factory::produce($request->query('type'), [$request, $response, $app]);
        if ($handler) {
            return $handler->execute($identifier);
        }
        throw new BadRequest(sprintf('Invalid type of %s', $request->query('type')));
    }
}