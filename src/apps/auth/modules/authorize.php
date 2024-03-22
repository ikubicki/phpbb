<?php

use phpbb\app;
use phpbb\errors\BadRequest;
use phpbb\request;
use phpbb\response;
use phpbb\utils\jwtAuth;

return function (request $request, response $response, ?app $app)
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
};
