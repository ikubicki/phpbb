<?php

namespace apps\auth\middleware;

use phpbb\app;
use phpbb\apps\middleware\abstraction;
use phpbb\core\accessRules;
use phpbb\request;
use phpbb\errors\NotAuthorized;
use phpbb\response;
use phpbb\utils\jwtAuth;

/**
 * JWT authentication middleware
 */
class jwtAuthMiddleware extends abstraction
{
    /**
     * Executes JWT validation
     * Throws NotAuthorized when JWT validation fails
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return request
     * @throws NotAuthorized
     */
    public function execute(request $request, response $response, app $app): request
    {
        $authorization = $request->client->bearer() ?: $request->cookie(
            $app->config->get('cookie')->raw('name')
        );
        $payload = jwtAuth::getPayload($authorization);
        if (!$payload || !$payload->sub) {
            throw new NotAuthorized($request);
        }
        if ($payload->sub) {
            $accessRules = new accessRules();
            $accessRules->loadPermissions($app, $payload->sub);
            $request->context->set('access', $accessRules);
        }
        $request->context->set('auth', $payload);
        $request->context->set('userId', $payload->sub ?? null);
        return $request;
    }
}