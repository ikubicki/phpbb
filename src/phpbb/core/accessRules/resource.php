<?php

namespace phpbb\core\accessRules;

use JsonSerializable;
use phpbb\errors\ServerError;
use stdClass;

class resource implements JsonSerializable
{

    const ANY = '*';
    const RESOURCES = [
        categories::RESOURCE,
        organisations::RESOURCE,
        policies::RESOURCE,
        users::RESOURCE,
        tags::RESOURCE,
        threads::RESOURCE,
    ];
    const RESOURCE = '';
    const ACCESS_RULES = [];

    /**
     * @var string $id
     */
    public string|array $id;

    /**
     * @var array $accessRules
     */
    protected array $accessRules = [];

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param string $id
     */
    public function __construct(string|array $id)
    {
        if (is_array($id)) {
            list($resource) = explode(':', reset($id));
            foreach($id as $singleId) {
                if (stripos($singleId, "$resource:") !== 0) {
                    throw new ServerError("Provided resources IDs are different kind.");
                }
            }
        }
        $this->id = $id;
    }

    /**
     * JSON serializer
     * 
     * @author ikubicki
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'resource' => static::RESOURCE,
            'accessRules' => $this->accessRules,
        ];
    }

    /**
     * Returns info as access structure
     * @return stdClass
     */
    public function asAccess(): stdClass
    {
        $access = [];
        if (is_array($this->id)) {
            $access['resources'] = $this->id;
        }
        else {
            $access['resource'] = $this->id;
        }
        $access['access'] = $this->accessRules;
        return (object) $access;
    }
    
    /**
     * Returns access rules
     * 
     * @author ikubicki
     * @return array
     */
    public function getAccessRules(): array
    {
        return $this->accessRules;
    }

    /**
     * Adds access rules
     * 
     * @author ikubicki
     * @param array $accessRules
     * @return static
     */
    public function addAccessRules(array $accessRules): static
    {
        $this->accessRules = array_unique(array_merge(
            $this->accessRules, 
            array_filter($accessRules, [$this, 'filterAccessRules'])
        ));
        if (in_array(self::ANY, $this->accessRules)) {
            $this->accessRules = array_merge($this->accessRules, static::ACCESS_RULES);
            $this->accessRules = array_filter($this->accessRules, function($accessRule) {
                return $accessRule != self::ANY;
            });
        }
        $this->accessRules = array_values(array_unique($this->accessRules));
        return $this;
    }

    /**
     * Filter out access rules
     * 
     * @author ikubicki
     * @param string $accessRule
     * @return bool
     */
    private function filterAccessRules(string $accessRule): bool
    {
        if ($accessRule == self::ANY) {
            return true;
        }
        return in_array($accessRule, static::ACCESS_RULES);
    }

    /**
     * Creates an instance of a resource
     * 
     * @author ikubicki
     * @param string $resource
     * @param string|array $id
     * @return static
     */
    public static function produce(string $resource, string|array $id): static
    {
        $class = sprintf('%s\%s', __NAMESPACE__, $resource);
        return new $class($id);
    }
}