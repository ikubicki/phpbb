<?php

use phpbb\app;
use phpbb\request;
use phpbb\response;

return function (request $request, response $response, ?app $app)
{

    $response->send([
        'call' => 'me',
        'params' => $request->params,
        'body' => $request->body(),
        'auth' => $request->context('auth'),
    ]);
};
