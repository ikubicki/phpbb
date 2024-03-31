<?php

namespace apps\auth\modules;

use apps\auth\middleware\jwtAuthMiddleware;
use apps\auth\middleware\permissionsMiddleware;
use phpbb\app;
use phpbb\apps\api\standardMethods;
use phpbb\core\accessRules\policies as AccessRulesPolicies;
use phpbb\core\accessRules\resource;
use phpbb\core\accessRules\users as AccessRulesUsers;
use phpbb\db\errors\DuplicateError;
use phpbb\db\errors\FieldError;
use phpbb\errors\BadRequest;
use phpbb\errors\NotAuthorized;
use phpbb\errors\ResourceNotFound;
use phpbb\request;
use phpbb\response;

class users extends standardMethods
{
    const COLLECTION = 'users';

    /**
     * Setups application routes
     * 
     * @author ikubicki
     * @return void
     */
    public function setup()
    {
        $this->app->get('/users', [$this, 'getRecords'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesUsers::VIEW]),
            ]
        ]);
        $this->app->post('/users', [$this, 'createUser'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesUsers::CREATE]),
            ]
        ]);
        $this->app->get('/users/:id', [$this, 'getRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesUsers::VIEW]),
            ]
        ]);
        $this->app->get('/users/:id/permissions', [$this, 'getRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesPolicies::VIEW]),
            ]
        ]);
        $this->app->patch('/users/:id/permissions', [$this, 'getRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesPolicies::EDIT]),
            ]
        ]);
        $this->app->patch('/users/:id', [$this, 'patchRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesUsers::EDIT]),
            ]
        ]);
        $this->app->delete('/users/:id', [$this, 'deleteUser'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesUsers::DELETE]),
            ]
        ]);
        $this->app->get('/me', [$this, 'getMe'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
            ]
        ]);
    }

    /**
     * Handles POST /users request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws BadRequest
     */
    public function createUser(request $request, response $response, app $app): response
    {
        $db = $app->plugin('db');
        $organisations = (array) $db->collection('organisations')->find([
            'default' => true,
            'type' => 'group',
        ]);
        if (!count($organisations)) {
            error_log("No default groups for users");
        }

        try {
            $user = $db->collection('users')->create();
            $user->setMany($request->body->toArray());
            $user->save();

            $user->addMemberships($organisations);
            $user->addAccessRules($user, resource::ANY);
        }
        catch(DuplicateError $error) {
            throw new BadRequest(sprintf(
                BadRequest::FIELDS_VALUES_TAKEN, join(',', $error->fields)
            ));
        }
        catch(FieldError $error) {
            throw new BadRequest($error->error);
        }
        return $response->status($response::OK)->send($user);
    }

    /**
     * Handles DELETE /users/:id request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws ResourceNotFound
     */
    public function deleteUser(request $request, response $response, app $app): response
    {
        if (empty($request->uri->param('id'))) {
            throw new ResourceNotFound($request);
        }
        $user = $app->plugin('db')->collection('users')->findOne([
            'uuid' => $request->uri->param('id')
        ]);
        if (!$user) {
            throw new ResourceNotFound($request);
        }
        $user->dropMemberships();
        $user->dropAccessRules();
        $user->delete();
        return $response->status($response::NO_CONTENT);
    }

    /**
     * Handles GET /me request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws NotAuthorized
     * @throws ResourceNotFound
     */
    public function getMe(request $request, response $response, app $app): response
    {
        $auth = $request->context->get('auth');
        $userId = $request->context->get('userId');
        if (!$userId) {
            throw new NotAuthorized($request);
        }
        $user = $app
            ->plugin('db')
            ->collection('users')
            ->findOne(['uuid' => $userId]);
        if (!$user) {
            throw new ResourceNotFound($request);
        }
        return $response->send([
            'expires' => $auth->raw('exp', 0),
            'remaining' => $auth->raw('exp', 0) - time(),
            'user' => $user,
        ]);
    }
}