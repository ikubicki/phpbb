<?php

namespace apps\auth\schemas;

use phpbb\db\collection\entity;
use phpbb\db\collection\field;

/**
 * Organisations collection entity
 */
class organisations extends entity
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
            ->field('parent', null, field::TYPE_UUID)
            ->field('type', 'group', field::enum(['group', 'team', 'set']))
            ->field('name')
            ->field('description')
            ->field('default', false, field::TYPE_BOOLEAN)
            ->field('creator', null, field::TYPE_UUID)
            ->field('created', null, field::TYPE_UNIXTIME, false, field::ON_CREATE)
            ->field('modified', null, field::TYPE_UNIXTIME, false, field::ON_UPDATE)
            ->index('uuid', field::INDEX_PRIMARY)
            ->index('name', field::INDEX_PRIMARY)
            ->reference('parent', organisations::class, 'parent')
            ->reference('creator', users::class, 'uuid')
            ->validate('name', field::VALIDATE_LENGTH, 5);
    }

    /**
     * Sets metadata field value
     * 
     * @author ikubicki
     * @param string $field
     * @param mixed $value
     * @return organisations
     */
    public function metadata(string $field, mixed $value): organisations
    {
        $this->metadata->$field = $value;
        return $this;
    }

    /**
     * Adds member to the organisation
     * 
     * @author ikubicki
     * @param string|object $member
     * @return organisations
     */
    public function addMemberships(string|object $member): organisations
    {
        if ($member instanceof entity) {
            $member = $member->uuid;
        }
        $membershipsCollection = $this->db->collection('memberships');
        $membership = $membershipsCollection->findOne(['member' => $member]);
        if(!$membership) {
            $membership = $membershipsCollection->create();
            $membership->organisations = [];
            $membership->member = $member;
        }
        if (!in_array($this->uuid, $membership->organisations)) {
            $organisations = $membership->organisations;
            $organisations[] = $this->uuid;
            $membership->organisations = $organisations;
            $membership->save();
        }
        return $this;
    }

    /**
     * Adds access rules for the principal resource
     * 
     * @author ikubicki
     * @param string|entity $principal
     * @param mixed $accessRules
     * @return organisations
     */
    public function addAccessRules(string|entity $principal, mixed $accessRules): organisations
    {
        policies::addAccessRulesToEntity($this, $principal, $accessRules);
        return $this;
    }

    /**
     * Drops all user memberships
     * 
     * @author ikubicki
     * @return organisations
     */
    public function dropMemberships(): organisations
    {
        $this->db
            ->collection('memberships')
            ->remove(['organisations' => $this->uuid]);
        return $this;
    }

    /**
     * Drops all access rules for the user resource
     * 
     * @author ikubicki
     * @return organisations
     */
    public function dropAccessRules(): organisations
    {
        $this->db
            ->collection('policies')
            ->remove(['principal' => $this->uuid]);
        return $this;
    }
}
