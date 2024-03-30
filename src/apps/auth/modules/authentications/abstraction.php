<?php

namespace apps\auth\modules\authentications;

use apps\auth\schemas\authentications;
use phpbb\app;
use phpbb\errors\BadRequest;
use phpbb\request;
use phpbb\response;
use phpbb\utils\jwtAuth;

abstract class abstraction
{

    /**
     * @var request $request
     */
    protected request $request;

    /**
     * @var response $response
     */
    protected response $response;

    /**
     * @var app $app
     */
    protected app $app;

    /**
     * Handles authentication method
     * 
     * @abstract
     * @author ikubicki
     * @param string $identifier
     * @return response
     */
    abstract public function execute(string $identifier): response;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     */
    public function __construct(request $request, response $response, app $app)
    {
        $this->request = $request;
        $this->response = $response;
        $this->app = $app;
    }

    /**
     * Returns stored authentication method
     * 
     * @author ikubicki
     * @param string $type
     * @param string $identifier
     * @return ?authentications
     * @throws BadRequest
     */
    protected function getAuthentication(string $type, string $identifier): ?authentications
    {
        $authentication = $this->app->plugin('db')
            ->collection('authentications')
            ->findOne([
                'type' => $type,
                'identifier' => $identifier,
            ]);
        if (!$authentication) {
            throw new BadRequest("Invalid authentication details");
        }
        return $authentication;
    }

    /**
     * Builds a JWT token for the authentication
     * 
     * @author ikubicki
     * @param authentications $authentication
     * @return response
     */
    protected function getAccessToken(authentications $authentication): response
    {
        $payload = [
            'tok' => 'access',
            'sub' => $authentication->owner,
            'iss' => $this->request->http->host,
            'exp' => time() + 86400,
            'scope' => 'phpbb'
        ];
        
        $jwt = jwtAuth::getJwt($payload);
        $cookieName = $this->app->config->get('cookie')->raw('name');
        if ($cookieName) {
            $this->response->cookie($cookieName, $jwt);
        }
        return $this->response->send([
            'expires' => $payload['exp'],
            'remaining' => $payload['exp'] - time(),
            'access_token' => $jwt,
        ]);
    }
}