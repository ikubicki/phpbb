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
        $resource = accessRules::getResource($resourceId);
        $resource->addAccessRules($accessRules);
        
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
     * @param resource|string $resource
     * @return bool
     */
    private function isMatchingResource(stdClass $rule, resource|string $resource): bool
    {
        $resourceId = $resource;
        if ($resource instanceof resource) {
            $resourceId = $resource->id;
        }
        if (is_array($resourceId)) {
            $isMatchingResource = true;
            foreach($resourceId as $singleResourceId) {
                $isMatchingResource = $isMatchingResource && $this->isMatchingResource($rule, $singleResourceId);
            }
            return $isMatchingResource;
        }
        list($collection) = explode(':', $resourceId);
        $ruleResources = $rule->resources ?? (array) $rule->resource ?? [];
        return in_array(resource::ANY, $ruleResources) ||
            in_array($collection . ':' . resource::ANY, $ruleResources) ||
            in_array($resourceId, $ruleResources);
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
}
