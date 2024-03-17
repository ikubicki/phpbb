<?php

namespace phpbb\db\collection;

use phpbb\db;

/**
 * Reference definition
 */
class reference
{

    /**
     * @var db $db
     */
    private db $db;

    /**
     * @var string $collection
     */
    private string $collection;

    /**
     * @var string $field
     */
    private string $field;


    /**
     * The constuctor
     * 
     * @author ikubicki
     * @param db $db
     * @param string $class
     * @param string $field
     */
    public function __construct(db $db, string $class, string $field)
    {
        $this->db = $db;
        $this->collection = substr($class, strrpos($class, '\\') + 1);
        $this->field = $field;
    }

    /**
     * Retrieves entity of referenced object
     * 
     * @author ikubicki
     * @param entity $sibling
     * @return entity|null
     */
    public function getEntity(entity $sibling): ?entity
    {
        $referenceId = $sibling->__get($this->field);
        if (!$referenceId) {
            return null;
        }
        return $this->db->collection($this->collection)->findOne($referenceId);
    }
}