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
     * @return response
     * @throws BadRequest
     */
    public function execute(): response
    {
        $identifier = $this->request->body->raw('identifier') ?: $this->request->query('identifier');
        $credential = $this->request->body->raw('credential');
        $authentication = $this->getAuthentication('password', $identifier);
    
        if (!$authentication->verify($credential)) {
            throw new BadRequest("Invalid authentication details");
        }
        return $this->getAccessToken($authentication);
    }
}