<?php

use phpbb\app;
use phpbb\request;
use phpbb\response;
use phpbb\errors\ServerError;

return function (request $request, response $response, app $app)
{
    $auth = $request->context('auth');
    $subject = $auth->sub ?? '';
    if (!$subject) {
        throw new ServerError('Invalid authorization token');
    }
    $user = $app
        ->plugin('db')
        ->collection('users')
        ->findOne(['uuid' => $subject]);
    if (!$user) {
        throw new ServerError('User doesn\'t exists anymore.');
    }
    $response->send([
        'expires' => $auth->exp ?? 0,
        'remaining' => ($auth->exp ?? 0) - time(),
        'user' => $user,
    ]);
};
