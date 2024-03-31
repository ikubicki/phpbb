<?php 

namespace apps\auth\modules\authentications;

use phpbb\errors\BadRequest;
use phpbb\response;
use Throwable;

class facebook extends abstraction
{

    /**
     * Executes facebook authentication
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
        $authentication = $this->getAuthentication('facebook', $identifier);
        $authentication->checkScope($scope);

        $redirectionUrl = $this->app->url(
            'authorize/oauth?type=facebook&identifier=' . $this->request->query('identifier')
        );

        // redirect to facebook auth page
        if (!$this->request->query('code')) {
            $url = sprintf('https://www.facebook.com/v19.0/dialog/oauth?%s', http_build_query([
                'client_id' => getenv('FB_APP_ID'),
                'redirect_uri' => $redirectionUrl,
            ]));
            return $this->response->redirect($url);
        }

        // handles code param
        $facebookUserId = $this->getFacebookUserId($this->request->query('code'), $redirectionUrl);
        if (!$facebookUserId) {
            throw new BadRequest("Facebook authentication failed!");
        }
        if (!$authentication->verify($facebookUserId)) {
            throw new BadRequest("Facebook user not found!");
        }

        return $this->getAccessToken($authentication, $scope);
    }

    /**
     * Returns facebook user ID or null on failure
     * 
     * @author ikubicki
     * @param string $code
     * @param string $redirectionUrl
     * @return ?string
     */
    private function getFacebookUserId(string $code, string $redirectionUrl): ?string
    {
        try {
            $guzzle = new \GuzzleHttp\Client();

            // get access token with code
            $url = 'https://graph.facebook.com/v19.0/oauth/access_token?' . http_build_query([
                'client_id' => getenv('FB_APP_ID'),
                'redirect_uri' => $redirectionUrl,
                'client_secret' => getenv('FB_APP_SECRET'),
                'code' => $code,
            ]);

            $response = $guzzle->get($url);
            $data = json_decode($response->getBody()->getContents());

            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $data->access_token
                ]
            ];

            // get user info
            $url = 'https://graph.facebook.com/me';
            $response = $guzzle->get($url, $options);
            $data = json_decode($response->getBody()->getContents());
            return $data->id;
        }
        catch(Throwable $throwable) {
            return null;
        }
    }
}