<?php

namespace phpbb\db\collection;

use phpbb\db;

/**
 * Reference definition
 */
class reference
{

    /**
     * @var string $field
     */
    private string $field;

    /**
     * @var string $collection
     */
    private string $collection;

    /**
     * @var string $field
     */
    private string $referencedField;


    /**
     * The constuctor
     * 
     * @author ikubicki
     * @param string $field
     * @param string $collection
     * @param string $referencedField
     */
    public function __construct(string $field, string $collection, string $referencedField)
    {
        $this->field = $field;
        $this->collection = $collection;
        $this->referencedField = $referencedField;
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
        return $sibling->db()->collection($this->collection)->findOne(
            [$this->referencedField => $referenceId]
        );
    }
}