<?php

use phpbb\app;
use phpbb\request;
use phpbb\response;
use phpbb\utils\jwtAuth;

return function (request $request, response $response, ?app $app)
{
    $payload = [
        'sub' => '65ed9097bf831601d5430f4b',
        'iss' => $request->http->host,
        'exp' => time() + 3600000,
    ];
    $jwt = jwtAuth::getJwt($payload);
    $response->send([
        'url' => $app->url('/login/a/b'),
        'db' => $app->plugin('db'),
        'call' => 'authenticate',
        'params' => $request->params,
        'body' => $request->body(),
        'type' => $request->post('type'),
        'login' => $request->post('login'),
        'password' => $request->post('password'),
        'access_token' => $jwt,
    ]);
};
