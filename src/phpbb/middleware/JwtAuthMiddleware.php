<?php

namespace phpbb\middleware;

use phpbb\request;
use phpbb\errors\NotAuthorized;
use phpbb\utils\jwtAuth;

class jwtAuthMiddleware
{
    /**
     * Executes JWT validation
     * Throws NotAuthorized when JWT validation fails
     * 
     * @author ikubicki
     * @param request
     * @return request
     * @throws NotAuthorized
     */
    public function execute(request $request): request
    {
        $authorization = $request->client->bearer();
        $payload = jwtAuth::getPayload($authorization);
        if (!$payload || !$payload->sub) {
            throw new NotAuthorized($request);
        }
        $request->context->set('auth', $payload);
        $request->context->set('sub', $payload->sub ?? null);
        return $request;
    }
}