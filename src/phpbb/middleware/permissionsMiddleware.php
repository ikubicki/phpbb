<?php

namespace phpbb\middleware;

use phpbb\app;
use phpbb\core\accessRules;
use phpbb\request;
use phpbb\errors\NotAuthorized;
use phpbb\response;
use phpbb\utils\jwtAuth;

class permissionsMiddleware
{

    private array $permissions = [];

    public function __construct(array $permissions = [])
    {
        $this->permissions = $permissions;
    }

    public function execute(request $request, response $response, app $app): request
    {
        if (!$request->context->raw('sub')) {
            throw new NotAuthorized($request);
        }
        if ($request->context->get('access')) {
            $accessRules = new accessRules();
            $accessRules->loadPermissions($app, $request->context->raw('sub'));
            $request->context->set('access', $accessRules);
        }
        
        $allowed = false;
        foreach ($this->permissions as $permission) {
            list($collection) = explode('.', $permission);
            $resource = "$collection:{$request->uri->raw('id', '*')}";
            if ($accessRules->has($resource, $permission)) {
                $allowed = true;
                continue;
            }
        }
        if (!$allowed) {
            throw new NotAuthorized($request);
        }
        return $request;
    }
}