<?php

namespace apps\auth\modules;

use apps\auth\middleware\jwtAuthMiddleware;
use apps\auth\middleware\permissionsMiddleware;
use phpbb\app;
use phpbb\apps\api\standardMethods;
use phpbb\core\accessRules\organisations as AccessRulesOrganisations;
use phpbb\core\accessRules\resource;
use phpbb\db\errors\DuplicateError;
use phpbb\db\errors\FieldError;
use phpbb\errors\BadRequest;
use phpbb\errors\ResourceNotFound;
use phpbb\request;
use phpbb\response;

class organisations extends standardMethods
{

    const COLLECTION = 'organisations';

    /**
     * Setups application routes
     * 
     * @author ikubicki
     * @return void
     */
    public function setup()
    {
        $this->app->get('/organisations', [$this, 'getRecords'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesOrganisations::VIEW]),
            ]
        ]);
        $this->app->get('/organisations/:id', [$this, 'getRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesOrganisations::VIEW]),
            ]
        ]);
        $this->app->get('/organisations/:id/members', [$this, 'getRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesOrganisations::VIEW]),
            ]
        ]);
        $this->app->post('/organisations', [$this, 'createOrganisation'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesOrganisations::CREATE]),
            ]
        ]);
        $this->app->patch('/organisations/:id', [$this, 'patchRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesOrganisations::EDIT]),
            ]
        ]);
        $this->app->delete('/organisations/:id', [$this, 'deleteRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesOrganisations::DELETE]),
            ]
        ]);
        $this->app->post('/organisations/:id/members', [$this, 'createRecord'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesOrganisations::EXTEND]),
            ]
        ]);
    }


    /**
     * Handles POST /organisations request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws BadRequest
     */
    public function createOrganisation(request $request, response $response, app $app): response
    {
        $db = $app->plugin('db');

        try {
            $organisation = $db->collection('organisations')->create();
            $organisation->setMany($request->body->toArray());
            $organisation->creator = $request->context->get('userId');
            if ($organisation->default) {
                $organisation->default == $request->context->get('access')->canCreate('organisations', resource::ANY);
            }
            $organisation->save();

            $organisation->addMemberships($organisation->creator);
            $organisation->addAccessRules($organisation->creator, resource::ANY);
        }
        catch(DuplicateError $error) {
            throw new BadRequest(sprintf(
                BadRequest::FIELDS_VALUES_TAKEN, join(',', $error->fields)
            ));
        }
        catch(FieldError $error) {
            throw new BadRequest($error->error);
        }
        return $response->status($response::OK)->send($organisation);
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
}