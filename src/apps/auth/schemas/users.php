<?php

namespace apps\auth\schemas;

use phpbb\core\accessRules\users as AccessRulesUsers;
use phpbb\db\collection\entity;
use phpbb\db\collection\field;
use phpbb\errors\BadRequest;
use phpbb\utils\arrays;
use stdClass;

/**
 * Users collection entity
 */
class users extends entity
{

    /**
     * The constructor
     * 
     * @author ikubicki
     */
    public function __construct()
    {
        $this
            ->field('uuid', null, field::TYPE_UUID, false, field::ON_CREATE)
            ->field('name')
            ->field('status', 'inactive', field::enum(['inactive', 'active', 'locked', 'deleted']))
            ->field('metadata', new stdClass, field::TYPE_OBJECT)
            ->field('creator', null, field::TYPE_UUID)
            ->field('created', null, field::TYPE_UNIXTIME, false, field::ON_CREATE)
            ->field('modified', null, field::TYPE_UNIXTIME, false, field::ON_UPDATE)
            ->index('uuid', field::INDEX_PRIMARY)
            ->validate('name', field::VALIDATE_LENGTH, 5);
    }

    /**
     * Sets metadata field value
     * 
     * @author ikubicki
     * @param string $field
     * @param mixed $value
     * @return users
     */
    public function metadata(string $field, mixed $value): users
    {
        $this->metadata->$field = $value;
        return $this;
    }

    /**
     * Sets avatar URL
     * 
     * @author ikubicki
     * @param string $url
     * @return users
     */
    public function avatar(string $url): users
    {
        $this->metadata('avatar', (object) [
            'url' => $url
        ]);
        return $this;
    }

    /**
     * Adds user to organisations
     * 
     * @author ikubicki
     * @param array $organisations
     * @return users
     */
    public function addMemberships(array $organisations): users
    {
        $membershipsCollection = $this->db->collection('memberships');
        if (count($organisations)) {
            $membership = $membershipsCollection->findOne(['member' => $this->uuid]);
            if(!$membership) {
                $membership = $membershipsCollection->create();
                $membership->member = $this->uuid;
            }
            $membership->organisations = array_merge(
                $membership->organisations ?? [],
                arrays::extractUuids($organisations)
            );
            $membership->save();
        }
        return $this;
    }

    /**
     * Adds access rules for the user resource
     * 
     * @author ikubicki
     * @param string|entity $principal
     * @param mixed $accessRules
     * @return users
     */
    public function addAccessRules(string|entity $principal, mixed $accessRules): users
    {
        if ($principal instanceof entity) {
            $principal = $principal->uuid;
        }
        $policiesCollection = $this->db->collection('policies');
        $policy = $policiesCollection->findOne(['principal' => (string) $principal]);
        if (!$policy) {
            $policy = $policiesCollection->create();
            $policy->principal = (string) $principal;
        }
        $policy->addAccessRules($this, $accessRules);
        $policy->save();
        return $this;
    }

    /**
     * Drops all user memberships
     * 
     * @author ikubicki
     * @return users
     */
    public function dropMemberships(): users
    {
        $this->db
            ->collection('memberships')
            ->remove(['member' => $this->uuid]);
        return $this;
    }

    /**
     * Drops all access rules for the user resource
     * 
     * @author ikubicki
     * @return users
     */
    public function dropAccessRules(): users
    {
        $this->db
            ->collection('policies')
            ->remove(['principal' => $this->uuid]);
        return $this;
    }
}
