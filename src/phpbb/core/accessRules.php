<?php

namespace phpbb\core;

use JsonSerializable;
use phpbb\app;
use phpbb\core\accessRules\resource;
use phpbb\db\collection\entity;
use phpbb\db\connectors\records;
use phpbb\errors\ServerError;

class accessRules implements JsonSerializable
{
    
    const CREATE = 'create';
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

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
     * Adds multiple access policies definitions
     *
     * @author ikubicki
     * @param records $policies
     * @return accessRules
     */
    public function addMany(records $policies): accessRules
    {
        foreach($policies as $policy) {
            $this->add($policy);
        }
        return $this;
    }
    
    /**
     * Adds a single access policy definition
     *
     * @author ikubicki
     * @param object $policy
     * @return accessRules
     */
    public function add(object $policy): accessRules
    {
        foreach($policy->rules ?? [] as $rule) {
            $this->addRule($rule);
        }
        return $this;
    }
    
    /**
     * Adds access rule for a resource
     *
     * @author ikubicki
     * @param object $rule
     * @return accessRules
     */
    public function addRule(object $rule): accessRules
    {
        $resources = $this->extractResources($rule);
        $access = (array) $rule->access;
        foreach($resources as $resource) {
            list($resource, $id) = explode(':', $resource);
            if (empty($this->resources[$resource])) {
                $this->resources[$resource] = [];
            }
            if (empty($this->resources[$resource][$id])) {
                $this->resources[$resource][$id] = resource::produce($resource, $id);
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
     * Transforms rule resources
     *
     * @author ikubicki
     * @param object $rule
     * @return array
     */
    private function extractResources(object $rule): array
    {
        $resources = [];
        foreach(($rule->resources ?? (array) $rule->resource ?? []) as $resource) {
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
     * @param bool $strict
     * @return array
     */
    private function getPrincipals(app $app, array $uuids, bool $strict = false): array
    {
        if ($strict) {
            return $uuids;
        }
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
     * @param string $strict
     * @return accessRules
     */
    public function loadPermissions(app $app, string $uuid, bool $strict = false): accessRules
    {
        $principals = $this->getPrincipals($app, [$uuid], $strict);
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
    public function getAccessRules(array $resourceIds): array
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
        $rules = $this->getAccessRules([$resourceId]);
        return in_array($rule, $rules[$resourceId]);
    }

    /**
     * Checks permissions 
     * 
     * @author ikubicki
     * @param string $action
     * @param string|entity $resource
     * @param ?string $uuid
     * @return bool
     */
    public function can(string $action, string|entity $resource, ?string $uuid = null): bool
    {
        if ($resource instanceof entity) {
            $uuid = $resource->uuid;
            $resource = $resource->collection->name;
        }
        if (!$uuid) {
            return false;
        }
        return $this->has("$resource:$uuid", "$resource.$action");
    }

    /**
     * Checks create permissions
     * 
     * @author ikubicki
     * @param string|entity $resource
     * @param ?string $uuid
     * @return bool
     */
    public function canCreate(string|entity $resource, ?string $uuid = null): bool
    {
        return $this->can(self::CREATE, $resource, $uuid);
    }

    /**
     * Checks read permissions
     * 
     * @author ikubicki
     * @param string|entity $resource
     * @param ?string $uuid
     * @return bool
     */
    public function canView(string|entity $resource, ?string $uuid = null): bool
    {
        return $this->can(self::VIEW, $resource, $uuid);
    }

    /**
     * Checks update permissions
     * 
     * @author ikubicki
     * @param string|entity $resource
     * @param ?string $uuid
     * @return bool
     */
    public function canEdit(string|entity $resource, ?string $uuid = null): bool
    {
        return $this->can(self::EDIT, $resource, $uuid);
    }


    /**
     * Checks delete permissions
     * 
     * @author ikubicki
     * @param string|entity $resource
     * @param ?string $uuid
     * @return bool
     */
    public function canDelete(string|entity $resource, ?string $uuid = null): bool
    {
        return $this->can(self::DELETE, $resource, $uuid);
    }

    /**
     * Creates an instance of access rules resource
     * 
     * @author ikubicki
     * @param string|array|entity $resourceId
     * @return accessRules\resource
     */
    public static function getResource(string|entity $resourceId): resource
    {
        if ($resourceId instanceof entity) {
            $resource = $resourceId->collection->name ?? 'unknown';
            $resourceId = $resourceId->getResourceId();
        }
        else {
            list($resource) = explode(':', $resourceId);
        }
        return resource::produce($resource, $resourceId);
    }
}
