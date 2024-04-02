<?php

namespace apps\auth\schemas;

use phpbb\core\accessRules;
use phpbb\core\accessRules\resource;
use phpbb\db\collection\entity;
use phpbb\db\collection\field;
use phpbb\errors\ServerError;
use stdClass;

/**
 * Policies collection entity
 */
class policies extends entity
{

    /**
     * The constructor
     * 
     * @author ikubicki
     */
    public function __construct()
    {
        $this
            ->field('principal')
            ->field('rules', [], field::TYPE_ARRAY)
            ->index('principal', field::INDEX_PRIMARY);
    }

    /**
     * Adds rights to the policy object
     * 
     * @author ikubicki
     * @param entity|array|string $resourceId
     * @param string|array $accessRules
     * @return policies
     * @throws ServerError
     */
    public function addAccessRules(entity|array|string $resourceId, string|array $accessRules): policies
    {
        
        if (is_array($resourceId)) {
            $this->verifyResources($resourceId);
            foreach($resourceId as $singleResourceId) {
                $this->addAccessRules($singleResourceId, $accessRules);
            }
            return $this;
        }

        $resource = accessRules::getResource($resourceId);
        $resource->addAccessRules((array) $accessRules);
        
        $found = false;
        foreach($this->rules as $resourceRule) {
            if ($this->isMatchingResource($resourceRule, $resource)) {
                $found = true;
                if ($resourceRule->access == resource::ANY) {
                    break;
                }
                if ($accessRules == resource::ANY) {
                    $resourceRule->access = resource::ANY;
                    break;
                }
                if (!is_array($accessRules)) {
                    break;
                }
                $resourceRule->access = $resource->getAccessRules();
            }
        }
        if (!$found) {
            $resourceRules = $this->rules;
            $resourceRules[] = $resource->asAccess();
            $this->rules = $resourceRules;
        }
        $rules = $this->rules;
        foreach($rules as $i => $resourceRule) {
            if (empty($resourceRule->access)) {
                unset($rules[$i]);
            }
        }
        $this->rules = $rules;
        return $this;
    }
    
    /**
     * Checks if resourceId matches the rule resource information
     * 
     * @author ikubicki
     * @param stdClass $rule
     * @param accessRules\resource $resource
     * @return bool
     */
    private function isMatchingResource(stdClass $rule, resource $resource): bool
    {
        $ruleResources = $rule->resources ?? (array) $rule->resource ?? [];
        if (in_array($resource->id, $ruleResources)) {
            return true;
        }
        return false;
    }

    /**
     * Adds access rules to a given entity
     * 
     * @author ikubicki
     * @param entity $entity
     * @param string|entity $principal
     * @param mixed $accessRules
     * @return entity
     */
    public static function addAccessRulesToEntity(entity $entity, string|entity $principal, mixed $accessRules): entity
    {
        if ($principal instanceof entity) {
            $principal = $principal->uuid;
        }
        $policiesCollection = $entity->db->collection('policies');
        $policy = $policiesCollection->findOne(['principal' => (string) $principal]);
        if (!$policy) {
            $policy = $policiesCollection->create();
            $policy->principal = (string) $principal;
        }
        $policy->addAccessRules($entity, $accessRules);
        $policy->save();
        return $entity;
    }

    private function verifyResources($resourceId): void
    {
        if (is_array($resourceId)) {
            list($resource) = explode(':', reset($resourceId));
            foreach($resourceId as $singleResourceId) {
                if (stripos($singleResourceId, "$resource:") !== 0) {
                    throw new ServerError("Provided resources IDs are different kind.");
                }
            }
        }
    }
}
