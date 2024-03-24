<?php

namespace phpbb\core;

use JsonSerializable;
use phpbb\app;
use phpbb\core\accessRules\resource;
use phpbb\db\connectors\records;

class accessRules implements JsonSerializable
{
    
    /**
     * @var array $resources
     */
    private array $resources = [];
    
    /**
     * JSON serializer
     * 
     * @author ikubicki
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->resources;
    }
    
    /**
     * Adds multiple access rule definitions
     *
     * @author ikubicki
     * @param records $rules
     * @return accessRules
     */
    public function addMany(records $rules): accessRules
    {
        foreach($rules as $rule) {
            $this->add($rule);
        }
        return $this;
    }
    
    /**
     * Adds a single access rule definition
     *
     * @author ikubicki
     * @param object $rule
     * @return accessRules
     */
    public function add(object $rule): accessRules
    {
        if (count($rule->policies ?? [])) {
            foreach($rule->policies as $policy) {
                $this->addPolicy($policy);
            }
        }
        return $this;
    }
    
    /**
     * Adds access rule policy to a resource
     *
     * @author ikubicki
     * @param object $policy
     * @return accessRules
     */
    public function addPolicy(object $policy): accessRules
    {
        $resources = $this->extractResources($policy);
        $access = (array) $policy->access;
        foreach($resources as $resource) {
            list($resource, $id) = explode(':', $resource);
            if (empty($this->resources[$resource])) {
                $this->resources[$resource] = [];
            }
            if (empty($this->resources[$resource][$id])) {
                $class = sprintf('%s\accessRules\%s', __NAMESPACE__, $resource);
                $this->resources[$resource][$id] = new $class($id);
            }
            $this->resources[$resource][$id]->addAccessRules($access);
        }
        foreach($this->resources as $resources) {
            foreach($resources as $id => $resource) {
                if ($id == resource::ANY || empty($resources[resource::ANY])) {
                    continue;
                }
                $resource->addAccessRules($resources[resource::ANY]->getAccessRules());
            }
        }
        return $this;
    }
    
    /**
     * Transforms policy resources
     *
     * @author ikubicki
     * @param object $policy
     * @return array
     */
    private function extractResources(object $policy): array
    {
        $resources = [];
        foreach(($policy->resources ?? (array) $policy->resource ?? []) as $i => $resource) {
            if ($resource == resource::ANY) {
                $resource = resource::ANY . ':' . resource::ANY;
            }
            list($entity, $id) = explode(':', $resource);
            if ($entity == resource::ANY) {
                foreach(resource::RESOURCES as $entity) {
                    if (!in_array("$entity:$id", $resources)) {
                        $resources[] = "$entity:$id";
                    }
                }
            }
            else if (!in_array($resource, $resources)) {
                $resources[] = $resource;
            }
        }
        return $resources;
    }
    
    /**
     * Extracts memberships organisations
     *
     * @author ikubicki
     * @param object $value
     * @return array
     */
    private function extractMembershipOrganisations(object $value): array
    {
        if ($value && $value->organisations) {
            return $value->organisations;
        }
        return [];
    }
    
    /**
     * Extracts organisations for given uuids
     *
     * @author ikubicki
     * @param app $app
     * @param array $uuids
     * @return array
     */
    private function getPrincipals(app $app, array $uuids): array
    {
        $memberships = (array) $app->plugin('db')->collection('memberships')->find([
            'member' => $uuids
        ]);
        if (count($memberships)) {
            $organisations = array_map([$this, 'extractMembershipOrganisations'], $memberships);
            $organisations = call_user_func_array('array_merge', $organisations);
            $uuids = array_merge($uuids, $this->getPrincipals($app, $organisations));
        }
        return $uuids;
    }
    
    /**
     * Loads permissions for given uuid
     *
     * @author ikubicki
     * @param app $app
     * @param string $uuid
     * @return accessRules
     */
    public function loadPermissions(app $app, string $uuid): accessRules
    {
        $principals = $this->getPrincipals($app, [$uuid]);
        $policies = $app->plugin('db')->collection('policies');
        $this->addMany($policies->find([
            'principal' => $principals
        ]));
        return $this;
    }
    
    /**
     * Returns access rules for given uuids
     *
     * @author ikubicki
     * @param array $uuids
     * @return array
     */
    public function getRules(array $resourceIds): array
    {
        $results = [];
        foreach ($resourceIds as $resourceId) {
            list($resource) = explode(':', $resourceId);
            if (empty($this->resources[$resource])) {
                $results[$resourceId] = [];
                continue;
            }
            if (isset($this->resources[$resource][$resourceId])) {
                $results[$resourceId] = $this->resources[$resource][$resourceId]->getAccessRules();
            }
            if (isset($this->resources[$resource][resource::ANY])) {
                $results[$resourceId] = $this->resources[$resource][resource::ANY]->getAccessRules();
            }
        }
        return $results;
    }

    /**
     * Checks if rule exists for given resource
     * 
     * @author ikubicki
     * @param string $resourceId
     * @param string $rule
     * @return bool
     */
    public function has(string $resourceId, string $rule): bool
    {
        $rules = $this->getRules([$resourceId]);
        return in_array($rule, $rules[$resourceId]);
    }
}
