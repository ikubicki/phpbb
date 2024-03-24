<?php

namespace apps\auth\modules;

use phpbb\app;
use phpbb\apps\api\standardMethods;
use phpbb\core\accessRules\users;
use phpbb\errors\BadRequest;
use phpbb\middleware\JwtAuthMiddleware;
use phpbb\middleware\permissionsMiddleware;
use phpbb\request;
use phpbb\response;
use phpbb\utils\jwtAuth;

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
    }

    function postAuthorize(request $request, response $response, app $app)
    {
        $authentication = $app->plugin('db')->collection('authentications')->findOne([
            'type' => $request->body->raw('type'),
            'identifier' => $request->body->raw('identifier'),
        ]);

        if (!$authentication) {
            throw new BadRequest("Invalid authentiation details");
        }
    
        if (!$authentication->verify($request->body->raw('credential'))) {
            throw new BadRequest("Invalid authentiation details");
        }

        $payload = [
            'sub' => $authentication->owner,
            'iss' => $request->http->host,
            'exp' => time() + 86400,
        ];
        
        $jwt = jwtAuth::getJwt($payload);
        $response->send([
            'expires' => $payload['exp'],
            'remaining' => $payload['exp'] - time(),
            'access_token' => $jwt,
        ]);
    }
}