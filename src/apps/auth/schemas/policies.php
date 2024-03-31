<?php

namespace apps\auth\schemas;

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
     * @param entity|string $resource
     * @param string|array $accessRules
     * @return policies
     * @throws ServerError
     */
    public function addAccessRules(entity|string $resource, string|array $accessRules): policies
    {
        $resourceId = self::extractResourceIdentifier($resource);
        $found = false;
        foreach($this->rules as $resourceRule) {
            if ($this->isMatchingResource($resourceRule, $resourceId)) {
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
                $resourceRule->access = array_merge(
                    $resourceRule->access ?? [],
                    $accessRules
                );
            }
        }
        if (!$found) {
            $resourceRules = $this->rules;
            $resourceRules[] = [
                'resource' => $resourceId,
                'access' => $accessRules,
            ];
            $this->rules = $resourceRules;
        }
        return $this;
    }
    
    /**
     * Checks if resourceId matches the rule resource information
     * 
     * @author ikubicki
     * @param stdClass $rule
     * @param string $resourceId
     * @return bool
     */
    private function isMatchingResource(stdClass $rule, string $resourceId): bool
    {
        list($collection) = explode(':', $resourceId);
        $ruleResources = $rule->resources ?? (array) $rule->resource ?? [];
        return in_array(resource::ANY, $ruleResources) ||
            in_array($collection . ':' . resource::ANY, $ruleResources) ||
            in_array($resourceId, $ruleResources);
    }

    /**
     * Extracts collection name and resource identifier from given resource
     * 
     * @author ikubicki
     * @param entity|string $resource
     * @return string
     */
    public static function extractResourceIdentifier(entity|string $resource): string
    {
        if ($resource instanceof entity) {
            if ($resource->uuid ?? false && $resource->collection->name ?? false) {
                $collection = $resource->collection->name;
                $resource = "{$collection}:{$resource->uuid}";
            }
            else {
                throw new ServerError(sprintf(
                    'Unable to grant permissions for %s object without uuid identifier',
                    $resource->collection->name ?? 'unknown collection'
                ));
            }
        }
        return $resource;
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
