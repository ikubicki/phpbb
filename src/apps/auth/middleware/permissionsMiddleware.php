<?php

namespace apps\auth\middleware;

use phpbb\app;
use phpbb\apps\middleware\abstraction;
use phpbb\core\accessRules;
use phpbb\request;
use phpbb\response;
use phpbb\errors\NotAuthorized;

/**
 * Permissions check middleware
 */
class permissionsMiddleware extends abstraction
{

    /**
     * @var array $permissions
     */
    private array $permissions = [];

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param array $permissions
     */
    public function __construct(array $permissions = [])
    {
        $this->permissions = $permissions;
    }

    /**
     * Executes permissions check
     * Throws NotAuthorized when permissions check fails
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