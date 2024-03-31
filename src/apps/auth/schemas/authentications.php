<?php

namespace apps\auth\schemas;

use phpbb\db\collection\entity;
use phpbb\db\collection\field;
use phpbb\errors\BadRequest;
use phpbb\utils\hmac;

/**
 * Authentications collection entity
 */
class authentications extends entity
{

    /**
     * The constructor
     * 
     * @author ikubicki
     */
    public function __construct()
    {
        $this
            ->field('identifier')
            ->field('type', 'password', field::enum(['password', 'oauth', 'sso']))
            ->field('signature', null)
            ->field('kid', null)
            ->field('settings', null, field::TYPE_OBJECT)
            ->field('scopes', ['phpbb'], field::selection(['phpbb', 'admin']))
            ->index('type', field::INDEX_PRIMARY)
            ->index('identifier', field::INDEX_PRIMARY)
            ->hide(['signature', 'kid']);
    }

    /**
     * Verifies credential
     * 
     * @author ikubicki
     * @param string $credential
     * @return bool
     */
    public function verify(string $credential): bool
    {
        return hmac::hash($credential, $this->kid) == $this->signature;
    }

    /**
     * Verifies token scope
     * 
     * @author ikubicki
     * @param string $scope
     * @return void
     * @throws BadRequest
     */
    public function checkScope(string $scope): void
    {
        $scopes = array_diff(explode(',', $scope), $this->scopes);
        if (count($scopes)) {
            throw new BadRequest(sprintf('Invalid scopes: %s', join(', ', $scopes)));
        }
    }
}
