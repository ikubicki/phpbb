<?php 

namespace apps\auth\modules\authentications;

use phpbb\errors\BadRequest;
use phpbb\response;

class password extends abstraction
{

    /**
     * Executes password authentication
     * 
     * @author ikubicki
     * @param string $identifier
     * @param string $scope
     * @return response
     * @throws BadRequest
     */
    public function execute(string $identifier, string $scope): response
    {
        if (!$identifier) {
            throw new BadRequest("Authentication identifier is required!");
        }
        if (!$scope) {
            throw new BadRequest("Scope is required!");
        }
        $credential = $this->request->body->raw('credential');
        $authentication = $this->getAuthentication('password', $identifier);
        $authentication->checkScope($scope);
    
        if (!$authentication->verify($credential)) {
            throw new BadRequest("Invalid authentication details");
        }
        return $this->getAccessToken($authentication, $scope);
    }
}