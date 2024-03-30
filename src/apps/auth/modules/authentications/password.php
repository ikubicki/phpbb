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
     * @return response
     * @throws BadRequest
     */
    public function execute(string $identifier): response
    {
        if (!$identifier) {
            throw new BadRequest(sprintf("Authentication identifier is required!"));
        }
        $credential = $this->request->body->raw('credential');
        $authentication = $this->getAuthentication('password', $identifier);
    
        if (!$authentication->verify($credential)) {
            throw new BadRequest("Invalid authentication details");
        }
        return $this->getAccessToken($authentication);
    }
}