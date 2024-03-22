<?php

namespace phpbb\core\accessRules;

use JsonSerializable;

class resource implements JsonSerializable
{

    const ANY = '*';
    const RESOURCES = [
        categories::RESOURCE,
        organisations::RESOURCE,
        tags::RESOURCE,
        threads::RESOURCE,
        users::RESOURCE,
    ];
    const RESOURCE = '';
    const ACCESS_RULES = [];

    /**
     * @var string $id
     */
    protected string $id;

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
    public function __construct(string $id)
    {
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
     * @return resource
     */
    public function addAccessRules(array $accessRules): resource
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
        $this->accessRules = array_unique($this->accessRules);
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
}