<?php

namespace apps\auth\modules;

use phpbb\apps\api\standardMethods;
use phpbb\core\accessRules\organisations as AccessRulesOrganisations;
use phpbb\middleware\jwtAuthMiddleware;
use phpbb\middleware\permissionsMiddleware;

class organisations extends standardMethods
{

    const COLLECTION = 'organisations';

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
        $this->app->post('/organisations', [$this, 'createRecord'], [
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
}