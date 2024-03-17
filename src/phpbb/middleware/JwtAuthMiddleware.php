<?php

namespace phpbb\middleware;

use phpbb\request;
use phpbb\errors\NotAuthorized;
use phpbb\utils\jwtAuth;

class JwtAuthMiddleware
{
    /**
     * Executes JWT validation
     * Throws NotAuthorized when JWT validation fails
     * 
     * @author ikubicki
     * @param request
     * 
     * @throws NotAuthorized
     */
    public function execute(request $request)
    {
        $authorization = $request->client->bearer();
        $payload = jwtAuth::getPayload($authorization);
        if (!$payload || !$payload->sub) {
            throw new NotAuthorized($request);
        }
        $request->context->set('auth', $payload);
    }
}