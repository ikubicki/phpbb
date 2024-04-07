<?php

namespace apps\auth\modules;

use apps\auth\middleware\jwtAuthMiddleware;
use apps\auth\middleware\permissionsMiddleware;
use apps\auth\schemas\organisations as SchemasOrganisations;
use phpbb\app;
use phpbb\apps\api\standardMethods;
use phpbb\core\accessRules\organisations as AccessRulesOrganisations;
use phpbb\core\accessRules\policies as AccessRulesPolicies;
use phpbb\core\accessRules\resource;
use phpbb\db\connectors\records;
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
        $this->app->get('/organisations/:id/members', [$this, 'getOrganisationMembers'], [
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
        $this->app->delete('/organisations/:id', [$this, 'deleteOrganisation'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesOrganisations::DELETE]),
            ]
        ]);
        $this->app->post('/organisations/:id/members', [$this, 'addOrganisationMembers'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesOrganisations::EXTEND]),
            ]
        ]);
        $this->app->delete('/organisations/:id/members', [$this, 'removeOrganisationMembers'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesOrganisations::EXTEND]),
            ]
        ]);
        $this->app->get('/organisations/:id/permissions', [$this, 'getRecordPermissions'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesPolicies::VIEW]),
            ]
        ]);
        $this->app->patch('/organisations/:id/permissions', [$this, 'patchRecordPermissions'], [
            'preExecution' => [
                new jwtAuthMiddleware(),
                new permissionsMiddleware([AccessRulesPolicies::EDIT]),
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
        $access  = $request->context->get('access');

        try {
            $collection = $db->collection('organisations');
            $organisation = $collection->create();
            $organisation->setMany($request->body->toArray());
            $organisation->creator = $request->context->get('userId');
            if ($organisation->default) {
                $organisation->default == $request->context->get('access')->canCreate('organisations', resource::ANY);
            }
            if ($organisation->parent) {
                $parent = $collection->findOne(['uuid' => $organisation->parent]);
                if (!$parent) {
                    throw new BadRequest("Invalid parent reference");
                }
                if (!$access->canEdit($parent)) {
                    throw new BadRequest(sprintf('You\'re not authorized to use %s as parent organisation', $organisation->parent));
                }
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
     * Handles DELETE /organisations/:id request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws ResourceNotFound
     */
    public function deleteOrganisation(request $request, response $response, app $app): response
    {
        $organisation = $this->getOrganisationEntity($request, $app);
        $organisation->dropMemberships();
        $organisation->dropAccessRules();
        $organisation->delete();
        return $response->status($response::NO_CONTENT);
    }

    /**
     * Handles GET /organisations/:id/members request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws ResourceNotFound
     */
    public function getOrganisationMembers(request $request, response $response, app $app): response
    {
        $organisation = $this->getOrganisationEntity($request, $app);
        $members = $this->getOrganisationEntityMembers($organisation, $request, $app);
        return $response->send($members);
    }

    /**
     * Handles GET /organisations/:id/members request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws ResourceNotFound
     */
    public function addOrganisationMembers(request $request, response $response, app $app): response
    {
        $organisation = $this->getOrganisationEntity($request, $app);
        $organisation->addMembers((array) $request->body->toArray());
        $members = $this->getOrganisationEntityMembers($organisation, $request, $app);
        return $response->send($members);
    }

    /**
     * Handles GET /organisations/:id/members request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws ResourceNotFound
     */
    public function removeOrganisationMembers(request $request, response $response, app $app): response
    {
        $organisation = $this->getOrganisationEntity($request, $app);
        $organisation->removeMembers((array) $request->body->toArray());
        $members = $this->getOrganisationEntityMembers($organisation, $request, $app);
        return $response->send($members);
    }

    /**
     * Returns an entity of organisation
     * 
     * @author ikubicki
     * @param request $request
     * @param app $app
     * @return SchemasOrganisations
     */
    private function getOrganisationEntity(request $request, app $app): SchemasOrganisations
    {
        if (empty($request->uri->param('id'))) {
            throw new ResourceNotFound($request);
        }
        $organisation = $app->plugin('db')
            ->collection('organisations')
            ->findOne([
                'uuid' => $request->uri->param('id')
            ]);
        if (!$organisation) {
            throw new ResourceNotFound($request);
        }
        return $organisation;
    }

    /**
     * Returns collection of organisation members
     * 
     * @author ikubicki
     * @param SchemasOrganisations $organisation
     * @param request $request
     * @param app $app
     * @return records
     */
    private function getOrganisationEntityMembers(SchemasOrganisations $organisation, request $request, app $app): records
    {
        $members = $organisation->getMembers();
        foreach($members as $i => $member) {
            $members[$i] = $member->member;   
        }
        if ($request->query('references') == 'true') {
            $members = $app->plugin('db')
                ->collection('users')
                ->find(
                    ['uuid' => (array) $members], 
                    ['sort' => ['name' => SORT_ASC]]
                );
            foreach($members as $i => $member) {
                $members[$i] = [
                    'uuid' => $member->uuid,
                    'name' => $member->name,
                ];
            }
        }
        return $members;
    }
}