<?php

namespace apps\auth\modules;

use phpbb\app;
use phpbb\db\errors\DuplicateError;
use phpbb\db\errors\FieldError;
use phpbb\errors\BadRequest;
use phpbb\request;
use phpbb\response;
use phpbb\errors\ResourceNotFound;

class users
{

    public static function getUsers(request $request, response $response, app $app)
    {
        $query = [];
        if ($request->get('uuid')) {
            $query['uuid'] = $request->get('uuid');
        }
        if ($request->get('name')) {
            $query['name'] = $request->get('name');
        }
        $users = $app->plugin('db')->collection('users')->find($query);
        return $response->status($response::OK)->send($users);
    }

    public static function createUser(request $request, response $response, app $app)
    {
        try {
            $user = $app->plugin('db')->collection('users')->create();
            $user->setMany((array) $request->body());
            $user->save();
        }
        catch(DuplicateError) {
            throw new BadRequest("User name is already taken");
        }
        catch(FieldError $error) {
            throw new BadRequest($error->error);
        }
        return $response->status($response::OK)->send($user->export());
    }

    public static function getUser(request $request, response $response, app $app)
    {
        if (empty($request->params['userId'])) {
            throw new ResourceNotFound($request);
        }
        $user = $app->plugin('db')->collection('users')->findOne([
            'uuid' => $request->params['userId']
        ]);
        if (!$user) {
            throw new ResourceNotFound($request);
        }
        return $response->status($response::OK)->send($user);
    }

    public static function patchUser(request $request, response $response, app $app)
    {
        if (empty($request->params['userId'])) {
            throw new ResourceNotFound($request);
        }
        $user = $app->plugin('db')->collection('users')->findOne([
            'uuid' => $request->params['userId']
        ]);
        if (!$user) {
            throw new ResourceNotFound($request);
        }
        $user->setMany((array) $request->body());
        $user->save();
        return $response->status($response::OK)->send($user);
    }

    public static function deleteUser(request $request, response $response, app $app)
    {
        if (empty($request->params['userId'])) {
            throw new ResourceNotFound($request);
        }
        $user = $app->plugin('db')->collection('users')->findOne([
            'uuid' => $request->params['userId']
        ]);
        if (!$user) {
            throw new ResourceNotFound($request);
        }
        $user->delete();
        return $response->status($response::NO_CONTENT);
    }
}