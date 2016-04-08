<?php

namespace Members\Model;

use Pimcore\Model\AbstractModel;
use Pimcore\Model\Object\AbstractObject;

class Restriction extends AbstractModel
{

    /**
     * @var string
     */
    public $targetId;

    /**
     * @var integer
     */
    public $id = NULL;

    /**
     * @var boolean
     */
    public $inheritable;

    /**
     * @var boolean
     */
    public $inherited = false;

    /**
     * @var array
     */
    public $relatedGroups = array();

    /**
     * @param $id
     *
     * @return \Members\Model\Restriction
     */
    public static function getById($id) {

        $obj = new self;
        $obj->getDao()->getById($id);
        return $obj;

    }

    /**
     * @param $id
     *
     * @return \Members\Model\Restriction
     */
    public static function getByTargetId($id) {

        $obj = new self;
        $obj->getDao()->getByField('targetId', (int) $id);
        return $obj;
    }

    public static function findNextInherited( $docId = NULL, $docParentIds )
    {
        $obj = new self;
        $obj->getDao()->getNextInheritedParent($docId = NULL, $docParentIds);
        return $obj;

    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function setId( $id )
    {
        return $this->id = (int) $id;
    }

    /**
     * @return string
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * @param integer $targetId
     * @return static
     */
    public function setTargetId($targetId)
    {
        $this->targetId = (int) $targetId;
        return $this;
    }

    /**
     * @return array
     */
    public function getRelatedGroups()
    {
        return $this->relatedGroups;
    }

    /**
     * @param array $relatedGroups
     * @return static
     */
    public function setRelatedGroups($relatedGroups)
    {
        $relatedGroups = (array) $relatedGroups;

        $this->relatedGroups = array_map('intval', $relatedGroups);

        return $this;
    }

    /**
     * @return boolean
     */
    public function getInherited()
    {
        return $this->inherited;
    }

    /**
     * Alias for getInherited()
     *
     * @return boolean
     */
    public function isInherited()
    {
        return $this->getInherited();
    }

    /**
     * @param boolean $inherited
     * @return static
     */
    public function setInherited($inherited)
    {
        $this->inherited = (bool) $inherited;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getInheritable()
    {
        return $this->inheritable;
    }

    /**
     * @param boolean $inheritable
     * @return static
     */
    public function setInheritable($inheritable)
    {
        $this->inheritable = (bool) $inheritable;
        return $this;
    }

}
