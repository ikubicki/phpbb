<?php

namespace phpbb\middleware;

use phpbb\errors\NotAuthorized;
use phpbb\utils\jwtAuth;

class JwtAuthMiddleware
{
    public function execute($request, $response)
    {
        $authorization = $request->bearer();
        $payload = jwtAuth::getPayload($authorization);
        if (!$payload || !$payload->sub) {
            throw new NotAuthorized($request);
        }
        $request->context->set('auth', $payload);
    }
}