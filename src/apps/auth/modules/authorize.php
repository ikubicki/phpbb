<?php

use phpbb\app;
use phpbb\request;
use phpbb\response;
use phpbb\utils\jwtAuth;

return function (request $request, response $response, ?app $app)
{
    $payload = [
        'sub' => 'cfe2134e-1e69-47c5-b12d-05d47b94ff0c',
        'iss' => $request->http->host,
        'exp' => time() + 86400,
    ];
    $jwt = jwtAuth::getJwt($payload);
    $response->send([
        'expires' => $payload['exp'],
        'remaining' => $payload['exp'] - time(),
        'access_token' => $jwt,
    ]);
};
